<?php
/**
 * Copyright © 2018
 * "GameOfLife" - Brought to you by:
 * ___________                    ________                       __
 * \__    ___/___ _____    _____  \_____  \  __ _______    _____/  |_ __ __  _____
 *   |    |_/ __ \\__  \  /     \  /  / \  \|  |  \__  \  /    \   __\  |  \/     \
 *   |    |\  ___/ / __ \|  Y Y  \/   \_/.  \  |  // __ \|   |  \  | |  |  /  Y Y  \
 *   |____| \___  >____  /__|_|  /\_____\ \_/____/(____  /___|  /__| |____/|__|_|  /
 *              \/     \/      \/        \__>          \/     \/                 \/
 *                          https://github.com/Team-Quantum
 *                      .PolluX / https://github.com/RealPolluX
 *                            Created @ 2018-12-09 - 11:01
 *
 *
 * Simple implementation of https://en.wikipedia.org/wiki/Conway%27s_Game_of_Life
 * Drawn with canvas and calculations made in php.
 *
 * RULE #1
 * ------------------------------------------------
 * An alive cell with less then 2 or more than 4
 * neighbors dies.
 *
 * RULE #2
 * ------------------------------------------------
 * A dead cell with 3 neighbors turns alive.
 *
 */

// TODO: colors: black = living, white = dead cell
// TODO: drawing in frontend

// show errors on page
ini_set('display_errors', 0);
error_reporting(0);

const GAME_SIZE = 64;


/**
 * Generates a new game board with randomized living cells.
 *
 * @return array game board
 */
function generateGameBoard(): array
{
	$grid = [];

	for ($i = 0; $i < GAME_SIZE; $i++) { // x
		array_push($grid, []);

		for ($j = 0; $j < GAME_SIZE; $j++){ // y
			array_push($grid[$i], rand(0, 1));
		}
	}

	return $grid;
}

/**
 * Gets the number of living neighbors for a specific cell
 *
 * @param array $grid
 * @param $l
 * @param $m
 *
 * @return int number of living neighbors
 */
function getAliveNeighbors(array $grid, $l, $m): int
{
    $aliveNeighbours = 0;
    for ($i = -1; $i <= 1; $i++) {
        for ($j = -1; $j <= 1; $j++) {
            $aliveNeighbours += $grid[$l + $i][$m + $j];
        }
    }

    return $aliveNeighbours;
}

/**
 * Generates the next generation which
 * will be send to the frontend
 *
 * @param $grid
 * @param $height int which represents the board height (y)
 * @param $width int which represents the board width (x)
 *
 * @return array
 */
function getNextGeneration($grid, $height, $width): array
{
    $futureGrid = $grid;

    // Loop through all cells
    for ($l = 1; $l < $height - 1; $l++) {
        for ($m = 1; $m < $width - 1; $m++) {
            // get number of nearby cells, that are alive
            $aliveNeighbours = getAliveNeighbors($grid, $l, $m);

            // Update the grid (data from last tick) - already counted cell will be replaced
            $aliveNeighbours -= $grid[$l][$m];

            // RULE #1 :: no active members, death follows
            if (($grid[$l][$m] == 1) && ($aliveNeighbours < 2)) {
                $futureGrid[$l][$m] = 0;
            } // RULE #1 :: overpopulation, death follows
            elseif (($grid[$l][$m] == 1) && ($aliveNeighbours > 3)) {
                $futureGrid[$l][$m] = 0;
            } // RULE #2 :: new life
            elseif (($grid[$l][$m] == 0) && ($aliveNeighbours == 3)) {
                $futureGrid[$l][$m] = 1;
            } // no action necessary, copy to new/future array
            else {
                $futureGrid[$l][$m] = $grid[$l][$m];
            }
        }
    }

    return $futureGrid;
}

/**
 * Two requests are possible:
 * (1) normal get request
 * (2) get request with special header
 *
 * The normal request made against this script will deliver the frontend code (html, css, js) and
 * the modified request requires a json payload and the header "X_GAME_OF_LIFE". The response will be a
 * new json object with the data for the next tick.
 */
if (array_key_exists('HTTP_X_GAME_OF_LIFE', $_SERVER)) {
    // we got a json/update request, calculate next cycle
    header('Content-Type: application/json');

    // get json body
    $body = file_get_contents('php://input');

    // do calculations
    try {
        $jsonArray = json_decode($body);

        // on first request, there will be no data, so return the default array
        if (count($jsonArray) === 0) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
            exit(json_encode(generateGameBoard()));
        }

        $nextGeneration = getNextGeneration($jsonArray, GAME_SIZE, GAME_SIZE);
    } catch (Exception $exception) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error', true, 500);
        exit('{"type": "error", "message": "' . $exception->getMessage() . '"}');
    }

    // send content to frontend
    header($_SERVER['SERVER_PROTOCOL'] . ' 200 OK', true, 200);
    exit(json_encode($nextGeneration));

} else {
    // normal page request, send index html
    require 'page.html';
}