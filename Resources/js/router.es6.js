export default {
    get(path) {
        const routes = {
//<ROUTES>
        };

        if ( !routes[path] ) {
            return;
        }

        let args = [...arguments];
        args.shift();

        return this.route(routes[path]).apply(this, args);
    },

    route( route ) {
        return function () {
            let i = 0, args = [];
            for (i; arguments.length > i; i++) {
                if (arguments[i]) {
                    args[i] = arguments[i];
                }
            }

            let s = args.reduce(function (t, e, n) {
                return t.replace(':' + (n + 1), e);
            }, route);

            if ( -1 !== s.indexOf(':') ) {
                throw new Error('Path \'' + s + '\' is expecting more arguments');
            }

            let prefix = ( window.DEBUG ) ? window.DEBUG : '';

            return prefix + s;
        }
    }
};
