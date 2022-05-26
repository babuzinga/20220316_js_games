const
    canvas = document.querySelector('canvas'),
    c = canvas.getContext('2d'),
    keys = {
      w: { pressed: false },
      a: { pressed: false },
      s: { pressed: false },
      d: { pressed: false },
    },
    collisionsMap = [],
    boundaries = [],
    offset = { x: -785, y: -650 },
    speed = 5
;

canvas.width = 1024;
canvas.height = 576;

class Sprite {
  constructor({ position, velocity, image, frames = { max: 1 } }) {
    this.position = position;
    this.image = image;
    this.frames = frames;

    this.image.onload = () => {
      this.width = this.image.width / this.frames.max;
      this.height = this.image.height;
    }
  }

  draw() {
    c.drawImage(this.image, 0, 0, this.width, this.height, this.position.x, this.position.y, this.width, this.height);
  }
}

const background = new Sprite({
  position: { x: offset.x, y: offset.y },
  image: createImage('/images/game_03/Pellet Town.png')
});

const player = new Sprite({
  position: { x: 440, y: 230 },
  image: createImage('/images/game_03/playerDown.png'),
  frames: { max: 4 }
});

const foreground = new Sprite({
  position: { x: offset.x, y: offset.y },
  image: createImage('/images/game_03/foregroundObjects.png')
});

for (let i = 0; i < collisions.length; i += 70) {
  collisionsMap.push(collisions.slice(i, i + 70));
}

class Boundary {
  static width = 48;
  static height = 48;

  constructor({ position }) {
    this.position = position;
    this.width = 48;
    this.height = 48;
  }

  draw() {
    c.fillStyle = 'rgba(255,0,0,0.1)';
    c.fillRect(this.position.x, this.position.y, this.width, this.height);
  }
}

collisionsMap.forEach((row, i) => {
  row.forEach((symbol, j) => {
    if (symbol === 1025)
      boundaries.push(
          new Boundary({
            position: {
              x: j * Boundary.width + background.position.x,
              y: i * Boundary.height + background.position.y
            }
          })
      )
  })
})

//
const movables = [background, foreground, ...boundaries];

function rectangularCollision({ rectangle1, rectangle2 }) {
  return (
      rectangle1.position.x + rectangle1.width >= rectangle2.position.x &&
      rectangle1.position.x <= rectangle2.position.x + rectangle2.width &&
      rectangle1.position.y + rectangle1.height >= rectangle2.position.y &&
      rectangle1.position.y <= rectangle2.position.y + rectangle2.height
  )
}

function animate() {
  window.requestAnimationFrame(animate);
  background.draw();
  boundaries.forEach(boundary => {
    boundary.draw();
  })
  player.draw();
  foreground.draw();

  let moving = true;
  if (keys.w.pressed /*&& lastKey === 'w'*/) {
    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x, y: boundary.position.y + speed } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('y', speed);
  } else if (keys.s.pressed /*&& lastKey === 's'*/) {
    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x, y: boundary.position.y - speed } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('y', speed * -1);
  } else if (keys.a.pressed /*&& lastKey === 'a'*/) {
    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x + speed, y: boundary.position.y } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('x', speed);
  } else if (keys.d.pressed /*&& lastKey === 'd'*/) {
    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x - speed, y: boundary.position.y } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('x', speed * -1);
  }
}

animate();

let lastKey;
addEventListener('keydown', ({ key, keyCode }) => {
  /*console.log(key + ' ' + keyCode);*/
  switch (key) {
    case 'a': keys.a.pressed = true; lastKey = 'a'; break;
    case 'd': keys.d.pressed = true; lastKey = 'd'; break;
    case 'w': keys.w.pressed = true; lastKey = 'w'; break;
    case 's': keys.s.pressed = true; lastKey = 's'; break;
  }
})

addEventListener('keyup', ({ key, keyCode }) => {
  /*console.log(key + ' ' + keyCode);*/
  switch (key) {
    case 'a': keys.a.pressed = false; break;
    case 'd': keys.d.pressed = false; break;
    case 'w': keys.w.pressed = false; break;
    case 's': keys.s.pressed = false; break;
  }
})

function setPositionMovables(c, val) {
  movables.forEach(movable => { movable.position[c] += val; })
}

function createImage(imageSrc) {
  const image = new Image();
  image.src = imageSrc;
  return image;
}