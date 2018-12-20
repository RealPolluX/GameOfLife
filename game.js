// global vars
const gameSize = 64;
const canvasSize = 512;

let gameInterval = null;
let paused = false;

// Ready up the game board
const canvas = document.getElementById('board');
canvas.width = canvasSize;
canvas.height = canvasSize;

const context = canvas.getContext('2d');

function drawCanvas(jsonObject, context) {
    const scale = canvasSize / gameSize;
	
	// Clear the game board
    context.clearRect(0, 0, canvasSize, canvasSize);

    context.fillStyle = '#212121';
    context.globalAlpha = 0.85;

	// Iterate over all rows
    for (let row = 0; row < gameSize; row++) {
		// Iterate over all columns
        for (let column = 0; column < gameSize; column++) {
			// Cell is alive
            if (jsonObject[row][column] === 1) {
				// Draw the cell 
                context.fillRect(row * scale, column * scale, scale, scale);
            }
        }
    }
}

// Set button actions
// 	  Functions to pause and start/stop the game
document.getElementById('startBtn').onclick = function() {
	if (gameInterval === null) {
		let gameBoard = [];
		
        gameInterval = setInterval(async () => {
			gameBoard = await getNextState(gameBoard);
			drawCanvas(gameBoard, context);
		}, 100);
    }
};

document.getElementById('pauseBtn').onclick = function() {
	if (gameInterval !== null) {
		paused = !paused;
	}
};

document.getElementById('stopBtn').onclick = function() {
	if (gameInterval !== null) {
        clearInterval(gameInterval);
        gameInterval = null;
    }
};

// game 'loop'
async function getNextState(gameBoard) {
    if (paused) return;

    // get the next state from the server
    const response = await fetch('http://localhost:8080/index.php', {
        method: 'POST',
        body: JSON.stringify(gameBoard),
        headers: {
            'Content-Type': 'application/json',
            'X-Game-Of-Life': 1
        }
    });

    return await response.json();
}
