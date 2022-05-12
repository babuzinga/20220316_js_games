// https://www.youtube.com/watch?v=4q2vvZn5aoo

const canvas = document.getElementById('myCanvas'),
    c = canvas.getContext('2d'),
    gravity = 0.5
;

canvas.width = 1024; // window.innerWidth;
canvas.height = 500 // window.innerHeight;

class Player {
  constructor() {
    this.position = {
      x: 100,
      y: 100
    }
    // скорость
    this.velocity = {
      x: 0,
      y: 1
    }
    this.width = 30;
    this.height = 30;
  }

  draw() {
    c.fillStyle = 'red';
    c.fillRect(this.position.x, this.position.y, this.width, this.height);
  }

  update() {
    this.draw();
    this.position.x += this.velocity.x;
    this.position.y += this.velocity.y;

    // как только объект достигает нижней границы canvas, скорость объекта равно 0
    if (this.position.y + this.height + this.velocity.y <= canvas.height)
      this.velocity.y += gravity;
    else
      this.velocity.y = 0;
  }
}

class Platform {
  constructor({ x, y, image }) {
    this.position = {
      x,
      y
    }
    this.image = image;
    this.width = this.image.width;
    this.height = this.image.height;

  }

  draw() {
    c.drawImage(this.image, this.position.x, this.position.y);
  }
}
const image = new Image();
image.src = '/images/game_02/platform.png'; // 580x125

const player = new Player();
const platforms = [
  new Platform({ x: -1,   y: 375, image:image }),
  new Platform({ x: 577,  y: 375, image:image })
];

const keys = {
  right: {
    pressed: false
  },
  left: {
    pressed: false
  }
}

let scrollOffset = 0;

function animate() {
  requestAnimationFrame(animate);

  c.fillStyle = 'white';
  c.clearRect(0, 0, canvas.width, canvas.height);
  platforms.forEach(platform => {
    platform.draw();
  });
  player.update();

  // Установка скорости перемещения при нажатии клавиш
  if (keys.right.pressed && player.position.x < 400) {
    player.velocity.x = 5;
  } else if (keys.left.pressed && player.position.x > 100) {
    player.velocity.x = -5;
  } else {
    player.velocity.x = 0;

    if (keys.right.pressed) {
      scrollOffset += 5;
      platforms.forEach(platform => {
        platform.position.x -= 5;
      });
    } else if (keys.left.pressed) {
      scrollOffset -= 5;
      platforms.forEach(platform => {
        platform.position.x += 5;
      });
    }
  }

  console.log(scrollOffset);

  // Детектор столкновения платформ
  platforms.forEach(platform => {
    if (player.position.y + player.height <= platform.position.y &&
        player.position.y + player.height + player.velocity.y >= platform.position.y &&
        player.position.x + player.width >= platform.position.x &&
        player.position.x <= platform.position.x + platform.width) {
      player.velocity.y = 0
    }
  });

  if (scrollOffset > 2000) {
    console.log('Win!!!!');
  }
}

animate();

addEventListener('keydown', ({ keyCode }) => {
  switch (keyCode) {
    case 65:
      console.log('left');
      keys.left.pressed = true;
      break;

    case 68:
      console.log('right');
      keys.right.pressed = true;
      break;

    case 83:
      console.log('down');
      break;

    case 87:
      console.log('up');
      player.velocity.y -= 10;
      break;
  }
})

addEventListener('keyup', ({ keyCode }) => {
  switch (keyCode) {
    case 65:
      console.log('left');
      keys.left.pressed = false;
      break;

    case 68:
      console.log('right');
      keys.right.pressed = false;
      break;

    case 83:
      console.log('down');
      break;

    case 87:
      console.log('up');
      player.velocity.y -= 10;
      break;
  }
})