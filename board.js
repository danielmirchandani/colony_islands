/*global define: false */
/*jslint white: true */
define([
	'dojo/dom',
	'dojo/dom-style',
	'dojo/on',
	'dojox/gfx',
	'dojo/domReady!'
], function (dom, domStyle, on, gfx) {
	"use strict";

	var colors, playerColors, selectFillOff, selectFillOn, triangleSide;

	colors = {
		Any: "#FFF",
		Brick: "#800",
		Desert: "#FFB",
		Grain: "#EB4",
		Lumber: "#080",
		Ore: "#888",
		Water: "#66F",
		Wool: "#4F4",
		background: "#00C",
		label: "#000",
		robber: "#000",
		select: "#FFF"
	};

	// These colors have to correspond to the colors specified in colony.css
	playerColors = {
		0: "#FFF",
		1: "#F00",
		2: "#0F0",
		3: "#00F",
		4: "#FF0",
		5: "#0FF",
		6: "#F0F"
	};
	
	selectFillOff = [0, 0, 0, 0];
	
	selectFillOn = [255, 255, 255, 0.5];
	
	triangleSide = 50;



	function getFontSize() {
		return triangleSide * 12 / 50;
	}

	function getRoadHalfWidth() {
		return triangleSide / 20;
	}

	function getStroke() {
		return {
			width: triangleSide / 25
		};
	}

	function getTriangleHeight() {
		return triangleSide * Math.sqrt(3) / 2;
	}

	// This needs to exist because the angles in the database are in degrees and gfx needs radians
	function rotateFromOldAngle(group, degrees) {
		group.applyRightTransform(gfx.matrix.rotate(2 * Math.PI * degrees / 360));
	}

	function submitSelection(ID) {
		dom.byId('selectIDInput').value = ID;
		dom.byId('selectForm').submit();
	}




	// This needs to exist because the coordinates in the database assume triangleSide = 100 and that (0, 0) is in the middle
	function convertXCoordinate(oldX) {
		return oldX * triangleSide / 100 + 7 * getTriangleHeight();
	}

	// This needs to exist because the coordinates in the database assume triangleSide = 100 and that (0, 0) is in the middle
	function convertYCoordinate(oldY) {
		return oldY * triangleSide / 100 + 5.5 * triangleSide;
	}

	function getFont() {
		return {
			family: 'sans-serif',
			size: getFontSize() + 'pt'
		};
	}

	function getFontLastRolled() {
		return {
			family: 'sans-serif',
			size: getFontSize() + 'pt',
			weight: 'bold'
		};
	}

	function getTileVectors() {
		return [
			{x: -getTriangleHeight(), y: -triangleSide / 2},
			{x:                    0, y:     -triangleSide},
			{x:  getTriangleHeight(), y: -triangleSide / 2},
			{x:  getTriangleHeight(), y:  triangleSide / 2},
			{x:                    0, y:      triangleSide},
			{x: -getTriangleHeight(), y:  triangleSide / 2},
			// Repeat the first point to form a closed path
			{x: -getTriangleHeight(), y: -triangleSide / 2}
		];
	}



	function translateFromOldCoordinates(group, x, y) {
		group.applyRightTransform(gfx.matrix.translate(convertXCoordinate(x), convertYCoordinate(y)));
	}



	function createPort(surface, port) {
		var group, triangleGroup;

		group = surface.createGroup();
		translateFromOldCoordinates(group, port.x, port.y);

		// Draw the triangle of which points can trade
		triangleGroup = group.createGroup();
		rotateFromOldAngle(triangleGroup, port.angle);
		triangleGroup.createPolyline([
			{x: -triangleSide / 2, y: getTriangleHeight()},
			{x:                 0, y:                   0},
			{x:  triangleSide / 2, y: getTriangleHeight()}
		]).setStroke(getStroke());

		// Draw the circle with the color of the resource
		group.createCircle({
			cx: 0,
			cy: 0,
			r: triangleSide / 2
		}).setFill(colors[port.resource]).setStroke(getStroke());

		// Draw the text of how many to trade in
		group.createText({
			align: 'middle',
			text: port.amount,
			x: 0,
			y: getFontSize() / 2
		}).setFill(colors.label).setFont(getFont());
	}

	function createRoad(surface, road) {
		var group;

		if (!road.color || !road.angle) {
			return;
		}

		group = surface.createGroup();
		translateFromOldCoordinates(group, road.x, road.y);
		rotateFromOldAngle(group, road.angle);

		group.createPolyline([
			{x:  triangleSide / 2 - getRoadHalfWidth() * Math.sqrt(3) / 3, y:  getRoadHalfWidth()},
			{x:                                          triangleSide / 2, y:                   0},
			{x:  triangleSide / 2 - getRoadHalfWidth() * Math.sqrt(3) / 3, y: -getRoadHalfWidth()},
			{x: -triangleSide / 2 + getRoadHalfWidth() * Math.sqrt(3) / 3, y: -getRoadHalfWidth()},
			{x:                                         -triangleSide / 2, y:                   0},
			{x: -triangleSide / 2 + getRoadHalfWidth() * Math.sqrt(3) / 3, y:  getRoadHalfWidth()},
			// Repeat the first point to close the path
			{x:  triangleSide / 2 - getRoadHalfWidth() * Math.sqrt(3) / 3, y:  getRoadHalfWidth()}
		]).setFill(playerColors[road.color]).setStroke(getStroke());
	}

	function createTile(surface, tile, lastRolled) {
		var group = surface.createGroup();
		translateFromOldCoordinates(group, tile.x, tile.y);

		group.createPolyline(getTileVectors()).setFill(colors[tile.type]).setStroke(getStroke());

		// Draw the robber
		if (tile.robber) {
			group.createCircle({
				cx: 0,
				cy: triangleSide / 2,
				r: triangleSide / 4
			}).setFill(colors.robber);
		}

		// Draw the roll to get the resource and how often that roll occurs
		if (tile.diceRoll && tile.probability) {
			group.createText({
				align: 'middle',
				text: tile.diceRoll + '(' + tile.probability + ')',
				x: 0,
				y: getFontSize() / 2
			}).setFill(colors.label).setFont(lastRolled === tile.diceRoll ? getFontLastRolled() : getFont());
		}
	}

	function createTown(surface, town) {
		var group, polygon;

		if (!town.color || !town.type) {
			return;
		}

		group = surface.createGroup();
		translateFromOldCoordinates(group, town.x, town.y);
		group.applyRightTransform(gfx.matrix.scale(triangleSide / 50));

		if (1 === town.type) {
			polygon = group.createPolyline([
				{x: -7.5, y:   5},
				{x:  7.5, y:   5},
				{x:  7.5, y:  -5},
				{x:   10, y:  -5},
				{x:    0, y: -15},
				{x:  -10, y:  -5},
				{x: -7.5, y:  -5},
				{x: -7.5, y:   5},
				// Repeat the first point to close the path
				{x: -7.5, y:   5}
			]);
		} else { // (2 === town.type)
			polygon = group.createPolyline([
				{x: -15, y:  10},
				{x:  15, y:  10},
				{x:  15, y: -20},
				{x:   9, y: -20},
				{x:   9, y: -10},
				{x:   3, y: -10},
				{x:   3, y: -20},
				{x:  -3, y: -20},
				{x:  -3, y: -10},
				{x:  -9, y: -10},
				{x:  -9, y: -20},
				{x: -15, y: -20},
				// Repeat the first point to close the path
				{x: -15, y:  10}
			]);
		}
		polygon.setFill(playerColors[town.color]).setStroke(getStroke());
	}

	function mapRoad(surface, road, roadID) {
		var group, select;

		group = surface.createGroup();
		translateFromOldCoordinates(group, road.x, road.y);
		rotateFromOldAngle(group, road.angle);

		select = group.createPolyline([
			{x: -triangleSide / 2, y:                    0},
			{x:                 0, y:  getTriangleHeight()},
			{x:  triangleSide / 2, y:                    0},
			{x:                 0, y: -getTriangleHeight()},
			// Repeat the first point to close the path
			{x: -triangleSide / 2, y:               0}
		]).setFill(selectFillOff).setStroke(null);
		on(select, 'click', function () {
			submitSelection(roadID);
		});
		on(select, 'mouseenter', function () {
			select.setFill(selectFillOn);
			select.setStroke(getStroke());
		});
		on(select, 'mouseleave', function () {
			select.setFill(selectFillOff);
			select.setStroke(null);
		});
	}

	function mapTile(surface, tile, tileID) {
		var group, select;

		group = surface.createGroup();
		translateFromOldCoordinates(group, tile.x, tile.y);

		select = group.createPolyline(getTileVectors()).setFill(selectFillOff).setStroke(null);
		on(select, 'click', function () {
			submitSelection(tileID);
		});
		on(select, 'mouseenter', function () {
			select.setFill(selectFillOn);
			select.setStroke(getStroke());
		});
		on(select, 'mouseleave', function () {
			select.setFill(selectFillOff);
			select.setStroke(null);
		});
	}

	function mapTown(surface, town, townID, tiles) {
		var select, tile1, tile2, tile3;

		tile1 = tiles[town.tile1ID];
		tile2 = tiles[town.tile2ID];
		tile3 = tiles[town.tile3ID];

		select = surface.createPolyline([
			{x: convertXCoordinate(tile1.x), y: convertYCoordinate(tile1.y)},
			{x: convertXCoordinate(tile2.x), y: convertYCoordinate(tile2.y)},
			{x: convertXCoordinate(tile3.x), y: convertYCoordinate(tile3.y)},
			{x: convertXCoordinate(tile1.x), y: convertYCoordinate(tile1.y)}
		]).setFill(selectFillOff).setStroke(null);
		on(select, 'click', function () {
			submitSelection(townID);
		});
		on(select, 'mouseenter', function () {
			select.setFill(selectFillOn);
			select.setStroke(getStroke());
		});
		on(select, 'mouseleave', function () {
			select.setFill(selectFillOff);
			select.setStroke(null);
		});
	}



	function createBoard(surface, board) {
		var i, size;

		// Draw the background
		size = surface.getDimensions();
		surface.createRect({width: size.width, height: size.height}).setFill(colors.background);

		for (i in board.tiles) {
			createTile(surface, board.tiles[i], board.lastRolled);
		}
		for (i in board.ports) {
			createPort(surface, board.ports[i]);
		}
		for (i in board.roads) {
			createRoad(surface, board.roads[i]);
		}
		for (i in board.towns) {
			createTown(surface, board.towns[i]);
		}

		// The rest of this function has to do with selections
		if (!board.select) {
			return;
		}

		if ("road" === board.select) {
			for (i in board.roads) {
				mapRoad(surface, board.roads[i], i);
			}
		} else if ("tile" === board.select) {
			for (i in board.tiles) {
				mapTile(surface, board.tiles[i], i);
			}
		} else if ("town" === board.select) {
			for (i in board.towns) {
				mapTown(surface, board.towns[i], i, board.tiles);
			}
		}
	}

	return {
		create: function (node, board) {
			var surface;

			// Set the side length of the triangles based on how big the container is
			triangleSide = 2 * domStyle.get(node, 'width') / Math.sqrt(3) / 14;

			// 14 triangles wide
			// 11 triangle sides high
			surface = gfx.createSurface(node, 14 * getTriangleHeight(), 11 * triangleSide)
			surface.whenLoaded(function (surface) {
				createBoard(surface, board);
			});
			return surface;
		}
	};
});
