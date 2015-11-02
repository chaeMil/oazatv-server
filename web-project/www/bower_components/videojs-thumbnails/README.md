Video.js Thumbnails
===================
A plugin that allows you to configure thumbnails to display when the user is hovering over the progress bar or dragging it to seek.

[![Build Status](https://travis-ci.org/brightcove/videojs-thumbnails.svg?branch=master)](https://travis-ci.org/brightcove/videojs-thummbnails)


Using the Plugin
----------------
The plugin automatically registers itself when you include video.thumbnails.js in your page:

```html
<script src='videojs.thumbnails.js'></script>
```

You probably want to include the default stylesheet, too. It handles showing and hiding thumbnails while hovering over the progress bar and a quick animation during the transition:

```html
<link href="videojs.thumbnails.css" rel="stylesheet">
```

Once you have your video created, you can activate the thumbnails plugin. In the first argument to the plugin, you should pass an object whose properties are the time in seconds you wish to display your thumbnails. At minimum, you'll need a prerty `0` with a `src`: the thumbnail to display if the user were to hover over the beginning of the progress bar. If you add additional times, they'll partition the progress bar and change the image that is displayed when the user hovers over that area. If you wanted to display one thumbnail for the first five seconds of a video and then another for the rest of the time, you could do it like this:

```js
video.thumbnails({
  0: {
    src: 'http://example.com/thumbnail1.png',
    width: '120px'
  },
  5: {
    src: 'http://example.com/thumbnail2.png'
  }
});
```

For each thumbnail time period, you can specify any other style changes you'd like to change when the user enters that region of the progress bar. Check out example.html to see how that technique can be used to create multiple thumbnails out of a single, sprited image.

The `width` property on each time period lets us know what the visible portion of the thumbnail should be. This is so that thumbnails won't reach beyond the player and perhaps get cut off. It can be specified on each time period or on the `0` time period.
