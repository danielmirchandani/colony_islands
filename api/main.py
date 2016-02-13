import bcrypt
import flask
import hashlib
import json
import jwt
import MySQLdb

JWT_SECRET = 'secret'

api = flask.Flask(__name__)
api.debug = True
# mod_wsgi requires a WSGI object named 'application' to run
application = api

def get_db():
	return MySQLdb.connect(
		host=flask.request.environ['DB_HOST'],
		user=flask.request.environ['DB_USER'],
		passwd=flask.request.environ['DB_PASS'],
		db=flask.request.environ['DB_BASE']
	)

@api.route('/')
def hello_world():
	return 'Hello world!'

@api.route('/auth', methods=['POST'])
def auth():
	data = flask.request.get_json()
	if 'email' not in data:
		return (json.dumps({
			'error': '"email" field required',
		}), 400, [])
	if 'password' not in data:
		return (json.dumps({
			'error': '"password" field required',
		}), 400, [])
	db = get_db()
	playerRows = db.cursor()
	playerRows.execute('''
		SELECT
			ID,
			passwordHash
		FROM col_players
		WHERE emailAddress = %s
	''', (data['email'],))
	for playerRow in iter(playerRows.fetchone, None):
		ID = int(playerRow[0])
		passwordHash = str(playerRow[1])

		# Unfortunately, I've implemented passwords in a variety of
		# ways, so I have to check for each style of password.
		# Before using any real standard, passwords were just hashed
		# using MD5. These hashes should be replaced by hasing the
		# password again using a better algorithm.
		if passwordHash[0] != '$':
			if hashlib.md5(data['password']).hex_digest() != passwordHash:
				continue
		# bcrypt was a massive security upgrade from MD5. Unfortunately,
		# even this should be replaced since it doesn't identify if it
		# was hashed using a flawed implementation and Blowfish isn't a
		# standard algorithm.
		elif passwordHash.startswith('$2a$'):
			if bcrypt.hashpw(data['password'], passwordHash) != passwordHash:
				continue
		# Nothing else for now. Perhaps SHA512 since Python crypt()
		# supports it?
		else:
			continue
		return json.dumps({
			'token': jwt.encode({
				'ID': ID,
			}, JWT_SECRET, algorithm='HS256'),
		})
	return (json.dumps({
		'error': 'Unauthorized',
	}), 401, [])

@api.route('/player_info', methods=['GET'])
def player_info_get():
	if 'Authorization' not in flask.request.headers:
		return (json.dumps({
			'error': 'Not authorized'
		}), 401, [])
		return
	data = jwt.decode(flask.request.headers['Authorization'], JWT_SECRET, algorithms=['HS256'])
	if 'ID' not in data:
		return (json.dumps({
			'error': 'Malformed JWT missing "ID" field'
		}), 400, [])
		return
	db = get_db()
	info_rows = db.cursor()
	info_rows = execute('''
		SELECT
			displayName,
			emailAddress
		FROM col_players
		WHERE ID = %s
	''', (data['ID'], ))
	for info in iter(info_rows.fetchone, None):
		displayName = str(info[0])
		emailAddress = str(info[1])
		return json.dumps({
			'displayName': displayName,
			'emailAddress': emailAddress,
		})

if __name__ == "__main__":
	api.run()
