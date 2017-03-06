if ( !window.Netbull ) {
    window.Netbull = {};
}

window.Netbull.Router = (function() {

    /**
     * Get route
     * @param route
     * @returns {Function}
     */
    var route = function ( route ) {
        return function() {
            var i = 0, args = [];
            for (i; arguments.length > i; i++) {
                if ( arguments[i] ) {
                    args[i] = arguments[i];
                }
            }

            var s = args.reduce(function (t, e, n) {
                return t.replace(":" + (n + 1), e);
            }, route);

            if (-1 !== s.indexOf(":")) {
                throw new Error("Path '" + s + "' is expecting more arguments");
            }

            var prefix = '';
            if ( window.DEBUG ) {
                prefix = window.DEBUG;
            }

            return {
                html: prefix + s,
                json: prefix + s + '.json'
            };
        };
    };

    //<ROUTES>

    return this;
})();