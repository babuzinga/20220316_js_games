class Sprite {
  constructor({ position, velocity, image, frames = { max: 1 }, sprites = {} }) {
    this.position = position;
    this.image = image;
    this.frames = { ...frames, val: 0, elapsed: 0 };
    this.moving = false;
    this.sprites = sprites;
    this.velocity = velocity;
    this.image.onload = () => {
      this.width = this.image.width / this.frames.max;
      this.height = this.image.height;
    }
  }

  draw() {
    c.drawImage(
        this.image,

        this.frames.val * this.width,
        0,
        this.width,
        this.height,

        this.position.x,
        this.position.y,
        this.width,
        this.height
    );

    if (this.moving) {
      if (this.frames.max > 1) {
        this.frames.elapsed++;
      }

      if (this.frames.elapsed % 10 === 0) {
        if (this.frames.val < this.frames.max - 1)
          this.frames.val++;
        else
          this.frames.val = 0;

        this.frames.elapsed = 0;
      }
    }
  }
}

class Boundary {
  static s_width = 48;
  static s_height = 48;

  constructor({ position }) {
    this.position = position;
    this.width = 48;
    this.height = 48;
  }

  draw() {
    c.fillStyle = 'rgba(255,0,0,0.2)';
    c.fillRect(this.position.x, this.position.y, this.width, this.height);
  }
}

