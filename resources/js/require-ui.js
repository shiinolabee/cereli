;
(function( global, document ) {
	
var Require = function() {
		return Require.interface;
	},
	
	onsuccess    = {},
	onerror      = {},
	debug        = false,
	_Require     = global.Require,
	pages        = {};
	is           = Object.prototype.toString,
	hasProperty  = Object.prototype.hasOwnProperty,
	isArray      = function( arg ) { return is.call( arg ) === "[object Array]"; },
	isObject     = function( arg ) { return is.call( arg ) === "[object Object]"; },
	isString     = function( arg ) { return is.call( arg ) === "[object String]"; },
	isFunction   = function( arg ) { return is.call( arg ) === "[object Function]"; };

Require.interface = Require.prototype = {
	pageRegisters: Require.pageRegisters || [],
	init: function( items, callback ) {
		this.each(items, function( item, i, size ) {
			if( !isFunction( item )) {
				if(isObject( item )) {
					var item = this.item( item );
					
					if(this.pageRegisters.indexOf( item.url ) == -1 ) {
						this.pageRegisters.push( item.url );
						this.include( item, function( o ) {
							if( o.callback ) { 
								o.callback.call( this );
							}
								
								
							if( cb = onsuccess[o.name] ) {
								this.each(cb, function( H ) {
									H();
								})
							}
							
							this.triggerHandler( callback, i, size );
						});
					}
				}
				else if(isArray( item )) {
					this.init( item );
				}
				else {
					if(this.pageRegisters.indexOf( item ) == -1) {
						this.pageRegisters.push( item );
						this.include( item, function( item ) {
							this.triggerHandler( callback, i, size );
						});
					};
				}
			}
		});
	},
	
	errors: function( check ) {
		this.error = check;
	},
	
	triggerHandler: function( callback, i, size ) {
		if( callback && isFunction( callback ) ) {
			var len = size - i;
			
			if( len == 1 ) {
				if( this.error && this.error == true ) {
					callback.call( this );
				}else {
					try {
						callback.call( this );
					}catch(e){;}
				}
			}
		}
	},
	
	include: function( items, callback ) {
		var callback = callback || function() {}, 
			self = this;
		try{
			setTimeout(function() {
				var script    = undefined,
					target    = document.getElementsByTagName('head')[0] || document.head,
					url       = isObject( items ) ? items.url : items,
					link      = document.getElementsByTagName('link'),
					source    = target.lastChild,
					extension = url.split(".").pop();
				
					
				switch( extension ) {
					case 'css': script = document.createElement("link");
						script.rel = "stylesheet";
						script.type = "text/css";
						script.href = url;
						source = link[link.length-1] || target.lastChild;
						
						break;
					default:
						script = document.createElement("script");
						script.type = "text/javascript";
						script.src  = url;
						
						
				};
				
				script.onload = script.onreadystatechange = function() {
					this.onload = this.onreadystatechange = this.onerror = null;
					callback.call( self, items ); // this is for the ready handler
					
					if( y = items.dependents ) {
						x = isString(y) ? [y] : y;
						self.init( x );
					}
				};
				
				script.onerror = function( e ) {
					this.onerror = null;
					console.log( "Oops! script wasn't able to load\nSource : "+ url );
				};
				
				
				
				target.insertBefore( script, target.lastChild );
			}, 0 );
		}catch(e){;}
	},
	
	item: function( items ) {
		var map = {};
		for(key in items ) {
			if( !!map[key] ) continue;
			if( key != 'callback' && key != 'dependents' ) {
				map.name = key;
				map.url  = items[key];
			}else {	
				map[key] = items[key];
			}
		};
			
		return map;
	},
	
	each: function( args, callback ) {
		if( !args.length || !args ) return;
		for(var i=0, len=args.length, size=len; i<len; i++)(function( idx, context ) {
			if( isFunction( args[len-1] ) && i==0) {
				size = size - 1;
			}

			callback.call( context, args[idx], idx, size );
		})( i, this );
	}
};

Require.extend = Require.interface.extend = function( property, context ) {
	var property = property || {};
	var context  = context  || Require.prototype;
	
	for(var key in property) {
		context[key] = property[key];
	}
	
	return context;
};

Require.extend({
	load: function( file, callback ) { 
		if( arguments.length ) {
			var files = arguments;
			if(isArray( file ) && arguments.length == 2) {
				files = file;
			}
			
			this.init( files, callback );
		}
		
		
		return this;
	},
	
	ready: function( key, success, error ) {
		
		if( !success ) return;
		// console.log( onsuccess );
		var H = onsuccess[key];
		if( !!H ) {
			onsuccess[key].push( success );
		}else {
			onsuccess[key] = [success];
		}
	}
});

// Bind to window Object
global.Require = global._Require =  new Require;
	
})( this, document );

