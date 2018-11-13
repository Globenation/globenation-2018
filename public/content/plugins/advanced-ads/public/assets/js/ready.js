/**
  * based on domready (c) Dustin Diaz 2014 - License MIT
  * https://github.com/ded/domready
  */
advanced_ads_ready = ( function() {
  var fns = [], listener
    , doc = typeof document === 'object' && document
    , hack = doc && doc.documentElement.doScroll
    , domContentLoaded = 'DOMContentLoaded'
    , loaded = doc && (hack ? /^loaded|^c/ : /^loaded|^i|^c/).test(doc.readyState)

  if (!loaded && doc){
    listener = function () {
	  doc.removeEventListener(domContentLoaded, listener)
	  window.removeEventListener( "load", listener );
	  loaded = 1
	  while (listener = fns.shift()) listener()
	}
	  
	doc.addEventListener(domContentLoaded, listener )
	window.addEventListener( 'load', listener );
  }

  return function (fn) {
    loaded ? setTimeout(fn, 0) : fns.push(fn)
  }
} )();
