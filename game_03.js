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
    offset = { x: -785, y: -650 }
;

canvas.width = 1024;
canvas.height = 576;

const background = new Sprite({
  position: { x: offset.x, y: offset.y },
  image: createImage('/images/game_03/Pellet Town.png')
});

const player = new Sprite({
  position: { x: 440, y: 230 },
  image:    createImage('/images/game_03/playerDown.png'),
  frames: { max: 4 },
  velocity: 5,
  sprites: {
    down:   createImage('/images/game_03/playerDown.png'),
    left:   createImage('/images/game_03/playerLeft.png'),
    right:  createImage('/images/game_03/playerRight.png'),
    up:     createImage('/images/game_03/playerUp.png'),
  }
});

const foreground = new Sprite({
  position: { x: offset.x, y: offset.y },
  image: createImage('/images/game_03/foregroundObjects.png')
});

for (let i = 0; i < collisions.length; i += 70) {
  collisionsMap.push(collisions.slice(i, i + 70));
}

collisionsMap.forEach((row, i) => {
  row.forEach((symbol, j) => {
    if (symbol === 1025)
      boundaries.push(
          new Boundary({
            position: {
              x: j * Boundary.s_width + background.position.x,
              y: i * Boundary.s_height + background.position.y
            }
          })
      )
  })
})

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
  player.moving = false;

  if (keys.w.pressed /*&& lastKey === 'w'*/) {
    player.moving = true;
    player.image = player.sprites.up;

    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x, y: boundary.position.y + player.velocity } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('y', player.velocity);
  } else if (keys.s.pressed /*&& lastKey === 's'*/) {
    player.moving = true;
    player.image = player.sprites.down;

    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x, y: boundary.position.y - player.velocity } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('y', player.velocity * -1);
  } else if (keys.a.pressed /*&& lastKey === 'a'*/) {
    player.moving = true;
    player.image = player.sprites.left;

    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x + player.velocity, y: boundary.position.y } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('x', player.velocity);
  } else if (keys.d.pressed /*&& lastKey === 'd'*/) {
    player.moving = true;
    player.image = player.sprites.right;

    for (let i = 0; i < boundaries.length; i++) {
      const boundary = boundaries[i];
      if (rectangularCollision({
        rectangle1: player,
        rectangle2: { ...boundary, position: { x: boundary.position.x - player.velocity, y: boundary.position.y } }
      })) { console.log('colliding'); moving = false; break; }
    }

    if (moving) setPositionMovables('x', player.velocity * -1);
  }
}

animate();

addEventListener('keydown', ({ key, keyCode }) => {
  /*console.log(key + ' ' + keyCode);*/
  switch (key) {
    case 'a': keys.a.pressed = true; break;
    case 'd': keys.d.pressed = true; break;
    case 'w': keys.w.pressed = true; break;
    case 's': keys.s.pressed = true; break;
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