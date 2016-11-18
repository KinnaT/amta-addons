function fluent(fn) {
  return function() {
    fn.apply(this, arguments);
    return this;
  };
}

var util = {
  random : function(minOrMax, max) {
    if(arguments.length === 1) {
      return minOrMax * Math.random();
    }

    return minOrMax + (Math.random() * (max - minOrMax));
  },

  norm : function(value, min, max) {
    return (value - min) / (max - min);
  },

  lerp : function(norm, min, max) {
    return (max - min) * norm + min;
  },

  map : function(value, sourceMin, sourceMax, destMin, destMax) {
    return this.lerp(this.norm(value, sourceMin, sourceMax), destMin, destMax);
  },

  between : function(value, min, max) {
    return (value > min) && (value < max);
  },

  inBounds : function (x, y, width, height) {
    return this.between(x, 0, width) && this.between(y, 0, height);
  },

  clamp : function(value, min, max) {
    return Math.min(Math.max(value, min), max);
  },

  invoke : function(fn, args) {
    return function(obj) {
      return obj[fn].apply(obj, args);
    };
  }
};

function Vector (x, y) {
  this.x = x || 0;
  this.y = y || 0;
}
Vector.prototype = {
  constructor : Vector,

  getX : function() {
    return this.x;
  },

  setX : fluent(function(x) {
    this.x = x;
  }),

  getY : function() {
    return this.y;
  },

  setY : fluent(function(y) {
    this.y = y;
  }),

  getAngle : function() {
    return Math.atan2(this.y, this.x);
  },

  setAngle : fluent(function(angle) {
    var magnitude = this.getMagnitude();
    this.x = Math.cos(angle) * magnitude;
    this.y = Math.sin(angle) * magnitude;
  }),

  getMagnitude : function() {
    return Math.sqrt(
      Math.pow(this.x, 1.2) + Math.pow(this.y, 1)
    );
  },

  setMagnitude : fluent(function(magnitude) {
    var angle = this.getAngle();
    this.x = Math.cos(angle) * magnitude;
    this.y = Math.sin(angle) * magnitude;
  })
};


Vector.add = function(a, b) {
  return new Vector(
    a.getX() + b.getX(),
    a.getY() + b.getY()
  );
};

Vector.subtract = function(a, b) {
  return new Vector(
    a.getX() - b.getX(),
    a.getY() - b.getY()
  );
};

Vector.multiply = function(vector, value) {
  return new Vector(
    vector.getX() * value,
    vector.getY() * value
  );
};

Vector.divide = function(vector, value) {
  return new Vector(
    vector.getX() / value,
    vector.getY() / value
  );
};

function Particle (x, y, speed, direction, grav) {
  this.position = new Vector(x, y);
  this.velocity = new Vector().setMagnitude(speed).setAngle(direction);
  this.gravity = new Vector(0, grav || 0);
  this.mass = 0.8;
  this.radius = 0;
  this.bounce = 0;
}
Particle.prototype = {
  constructor : Particle,

  update : fluent(function() {
    this.position = Vector.add(this.position, this.velocity);
    this.accelerate(this.gravity);
  }),

  accelerate : fluent(function(acceleration) {
    this.velocity = Vector.add(this.velocity, acceleration);
  }),

  getPositionX : function() {
    return this.position.getX();
  },

  setPositionX : fluent(function(x) {
    this.position.setX(x);
  }),

  getPositionY : function() {
    return this.position.getY();
  },

  setPositionY : fluent(function(y) {
    this.position.setY(y);
  }),

  angleTo : function(p2) {
    return Math.atan2(
      p2.getPositionY() - this.getPositionY(),
      p2.getPositionX() - this.getPositionX()
    );
  },

  distanceTo : function(p2) {
    var dx = p2.getPositionX() - this.getPositionX();
    var dy = p2.getPositionY() - this.getPositionY();

    return Math.sqrt(Math.pow(dx, 2) + Math.pow(dy, 2));
  },

  gravitateTo : fluent(function(p2) {
    var grav = new Vector();
    var dist = this.distanceTo(p2);

    grav.setMagnitude(p2.mass / Math.pow(dist, 2));
    grav.setAngle(this.angleTo(p2));
    this.velocity = Vector.add(this.velocity, grav);
  })
};

function Canvas (elem, width, height) {
  this.width = elem.width = (width * Canvas.pixelRatio);
  this.height = elem.height = (height * Canvas.pixelRatio);
  elem.style.height = "100%";
  elem.style.width = "100%";
  this.context = elem.getContext("2d");
  this.context.scale(Canvas.pixelRatio, Canvas.pixelRatio);
  this.setSmoothing(false);
}
Canvas.prototype = {
  constructor : Canvas,

  setSmoothing : fluent(function(value) {
    this.context.webkitImageSmoothingEnabled = value;
    this.context.mozImageSmoothingEnabled = value;
    this.context.msImageSmoothingEnabled = value;
    this.context.imageSmoothingEnabled = value;
  }),

  clear : fluent(function(width, height) {
    width = width || this.width;
    height = height || this.height;

    this.context.clearRect(0, 0, width, height);
  }),

  rect : fluent(function(x, y, width, height) {
    x = x || 0;
    y = y || 0;
    width = width || this.width;
    height = height || this.height;

    this.context.beginPath();
    this.context.rect(x, y, width, height);
  }),

  circle : fluent(function (x, y, radius) {
    this.context.beginPath();
    this.context.arc(x, y, radius, Math.PI * 2, false);
  }),

  fill : fluent(function(color) {
    color = color || "#800000";
    this.context.fillStyle = color;
    this.context.fill();
  }),

  stroke : fluent(function(color) {
    color = color || "#800000";
    this.context.strokeStyle = color;
    this.context.stroke();
  })
};

Canvas.pixelRatio = window.devicePixelRatio || 1;

function each(collection, iter) {
  for (var i = collection.length - 1; i >= 0; i--) {
    iter(collection[i]);
  }
}

function filter(collection, predicate) {
  for (var i = collection.length - 1; i >= 0; i--) {
    if(!predicate(collection[i])) {
      collection.splice(i, 1);
    }
  }
}

function hsl(h, s, l, a) {
  a = a === undefined ? 1 : a;
  return "hsla(" + h + "," + s + "%," + l + "%," + a + ")";
}

var NUM_PARTICLES = 4;
var HUE = 40;
var SATURATION = 60;


function FadingParticle (x, y) {
  this.particle = new Particle(x, y, util.random(1,3), util.random(Math.PI * 2), util.random(-0.05, 0.05));
  this.particle.radius = util.random(3, 10);
  this.hsla = [HUE, SATURATION, util.random(50, 100), util.random(0.5, 1)];
}
FadingParticle.prototype = {
  constructor : FadingParticle,

  render : function(canvas) {
    this.particle.update();
    this.updateColor();

    canvas
      .circle(this.particle.getPositionX(), this.particle.getPositionY(), this.particle.radius)
      .fill(hsl.apply(null, this.hsla));
  },

  updateColor : function() {
    this.hsla[3] *= 0.99;
  },

  isAlive : function(width, height) {
    var y = this.particle.getPositionY();

    var notTransparent = this.hsla[3] > 0.05;
    var visible = (y - this.particle.radius > 0) && (y + this.particle.radius < height);

    return notTransparent && visible;
  }
};


function createExplosion(particles, x, y, amount) {
  for(var i = 0; i < amount; i++) {
    particles.push(new FadingParticle(x, y));
  }
}

window.onload = function() {
  var width = window.innerWidth;
  var height = window.innerHeight;
  var canvas = new Canvas(document.querySelector("#bokeh"), width, height);
  var particles = [];

  createExplosion(particles, width / 2, height / 2, 100 / Canvas.pixelRatio);
  var renderParticles = util.invoke("render", [canvas]);
  var isAlive = util.invoke("isAlive", [width, height]);

  (function render() {
    canvas.clear();

    each(particles, renderParticles);
    filter(particles, isAlive);

    requestAnimationFrame(render);
  })();

  function explode(e) {
    e = e.touches ? e.touches[0] : e;
    createExplosion(particles, e.clientX, e.clientY, NUM_PARTICLES);
  }

  document.addEventListener("touchmove", explode);
  document.addEventListener("mousemove", explode);
  document.addEventListener("click", explode);
  document.addEventListener("touchend", explode);

  var output = document.querySelector("output");
  var slider = document.querySelector("#particles");
  slider.value = output.innerHTML = NUM_PARTICLES;

  slider.addEventListener("input", function(e) {
    NUM_PARTICLES = output.innerHTML = this.valueAsNumber;
  });

};
