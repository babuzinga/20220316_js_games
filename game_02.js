// https://www.youtube.com/watch?v=4q2vvZn5aoo

const canvas = document.getElementById('myCanvas'),
    c = canvas.getContext('2d'),
    gravity = 0.5
;

canvas.width = 1024; // window.innerWidth;
canvas.height = 600 // window.innerHeight;

class Player {
  constructor() {
    this.position = { x: 100, y: 100  }
    this.velocity = { x: 0,   y: 1    } // скорость
    this.width    = 66;
    this.height   = 150;
    this.speed    = { x: 10,  y: 10   } // скорость перемещения

    this.frames = 0;
    this.sprites = {
      stand:  {
        right:  createImage('/images/game_02/spriteStandRight.png'),
        left:   createImage('/images/game_02/spriteStandLeft.png'),
        cropWidth: 177,
        width:  66
      },
      run:    {
        right:  createImage('/images/game_02/spriteRunRight.png'),
        left:   createImage('/images/game_02/spriteRunLeft.png'),
        cropWidth: 341,
        width:  127.875
      }
    }
    this.currentSprite = this.sprites.stand.right;
    this.currentCropWidth = this.sprites.stand.cropWidth;
  }

  draw() {
    /*c.fillStyle = 'red';
    c.fillRect(this.position.x, this.position.y, this.width, this.height);*/
    c.drawImage(
        this.currentSprite,
        this.currentCropWidth * this.frames,
        0,
        this.currentCropWidth,
        400,
        this.position.x,
        this.position.y,
        this.width,
        this.height
    );
  }

  update() {
    this.frames++;
    if (this.frames > 59 && (this.currentSprite === this.sprites.stand.right || this.currentSprite === this.sprites.stand.left)) this.frames = 0;
    else if (this.frames > 29 && (this.currentSprite === this.sprites.run.right || this.currentSprite === this.sprites.run.left)) this.frames = 0;

    this.draw();
    this.position.x += this.velocity.x;
    this.position.y += this.velocity.y;

    // как только объект достигает нижней границы canvas, скорость объекта равно 0
    if (this.position.y + this.height + this.velocity.y <= canvas.height) this.velocity.y += gravity;
    //else this.velocity.y = 0;
  }
}

class Platform {
  constructor({ x, y, image }) {
    this.position = { x, y }
    this.image    = image;
    this.width    = this.image.width;
    this.height   = this.image.height;
  }

  draw() {
    c.drawImage(this.image, this.position.x, this.position.y);
  }
}

class GenericObject {
  constructor({ x, y, parallax, image }) {
    this.position = { x, y }
    this.image    = image;
    this.width    = this.image.width;
    this.height   = this.image.height;
    this.parallax = parallax;
  }

  draw() {
    c.drawImage(this.image, this.position.x, this.position.y);
  }
}

function createImage(imageSrc) {
  const image = new Image();
  image.src = imageSrc;
  return image;
}

let scrollOffset,
    platformImage   = createImage('/images/game_02/platform.png'), // 580x125
    platformSmallTallImage  = createImage('/images/game_02/platformSmallTall.png'),
    player,
    platforms,
    genericObjects,
    keys = { right:  { pressed: false }, left:   { pressed: false } },
    lastKey
;

function init() {
  let top_position = canvas.height - platformImage.height;
  scrollOffset = 0;
  player = new Player();
  platforms = [
    new Platform({x: 400, y: canvas.height - 300, image: platformSmallTallImage}),

    new Platform({x: 0 * (platformImage.width - 3), y: top_position, image: platformImage}),
    new Platform({x: 1 * (platformImage.width - 3), y: top_position, image: platformImage}),
    new Platform({x: 2 * (platformImage.width - 3) + 200, y: top_position, image: platformImage}),
    new Platform({x: 3 * (platformImage.width - 3), y: top_position, image: platformImage}),
    new Platform({x: 4 * (platformImage.width - 3), y: top_position, image: platformImage}),
    new Platform({x: 5 * (platformImage.width - 3) + 300, y: top_position, image: platformImage}),
    new Platform({x: 6 * (platformImage.width - 3), y: top_position, image: platformImage}),
    new Platform({x: 7 * (platformImage.width - 3), y: top_position, image: platformImage})
  ];
  genericObjects = [
    new GenericObject({x: -1, y: -1, parallax: 0.1, image: createImage('/images/game_02/background.png')}),
    new GenericObject({x: -1, y: -1, parallax: 0.5, image: createImage('/images/game_02/hills.png')})
  ];
}

init();
animate();

// Отрисовка
function animate() {
  requestAnimationFrame(animate);

  c.fillStyle = 'white';
  c.clearRect(0, 0, canvas.width, canvas.height);

  genericObjects.forEach(generic => {
    generic.draw();
  });

  platforms.forEach(platform => {
    platform.draw();
  });
  player.update();

  // Установка скорости перемещения при нажатии клавиш
  if (keys.right.pressed && player.position.x < 500) {
    player.velocity.x = player.speed.x;
  } else if ((keys.left.pressed && player.position.x > 200) || (keys.left.pressed && scrollOffset === 0 && player.position.x > 0)) {
    player.velocity.x = -player.speed.x;
  } else {
    player.velocity.x = 0;

    if (keys.right.pressed) {
      scrollOffset += player.speed.x;
      platforms.forEach(platform => {
        platform.position.x -= player.speed.x;
      });
      genericObjects.forEach(generic => {
        generic.position.x -= generic.parallax;
      });
    } else if (keys.left.pressed && scrollOffset > 0) {
      scrollOffset -= player.speed.x;
      platforms.forEach(platform => {
        platform.position.x += player.speed.x;
      });
      genericObjects.forEach(generic => {
        generic.position.x += generic.parallax;
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

  if (keys.right.pressed && lastKey === 'right' && player.currentSprite !== player.sprites.run.right) {
    player.frames = 1;
    player.currentSprite = player.sprites.run.right;
    player.currentCropWidth = player.sprites.run.cropWidth;
    player.width = player.sprites.run.width;
  } else if (keys.left.pressed && lastKey === 'left' && player.currentSprite !== player.sprites.run.left) {
    player.frames = 1;
    player.currentSprite = player.sprites.run.left;
    player.currentCropWidth = player.sprites.run.cropWidth;
    player.width = player.sprites.run.width;
  } else if (!keys.left.pressed && lastKey === 'left' && player.currentSprite !== player.sprites.stand.left) {
    player.currentSprite = player.sprites.stand.left;
    player.currentCropWidth = player.sprites.stand.cropWidth;
    player.width = player.sprites.stand.width;
  } else if (!keys.right.pressed && lastKey === 'right' && player.currentSprite !== player.sprites.stand.right) {
    player.currentSprite = player.sprites.stand.right;
    player.currentCropWidth = player.sprites.stand.cropWidth;
    player.width = player.sprites.stand.width;
  }

  // Условие победы
  if (scrollOffset > 4000) {
    console.log('Win!!!!');
    init();
  }

  // Условия поражения
  if (player.position.y > canvas.height) {
    console.log('You lose!!!!');
    init();
  }
}

addEventListener('keydown', ({ keyCode }) => {
  //console.log(keyCode);
  switch (keyCode) {
    case 37:
    case 65:
      console.log('left');
      keys.left.pressed = true;
      lastKey = 'left';
      break;

    case 39:
    case 68:
      console.log('right');
      keys.right.pressed = true;
      lastKey = 'right';
      break;

    case 83:
      console.log('down');
      break;

    case 32:
    case 87:
      console.log('up');
      player.velocity.y -= player.speed.y;
      break;
  }
})

addEventListener('keyup', ({ keyCode }) => {
  switch (keyCode) {
    case 37:
    case 65:
      console.log('left');
      keys.left.pressed = false;
      break;

    case 39:
    case 68:
      console.log('right');
      keys.right.pressed = false;

    case 83:
      console.log('down');
      break;

    case 32:
    case 87:
      console.log('up');
      break;
  }
})