export default {
  get(path, ...args) {
    const routes = {
//<ROUTES>
    };

    if (!routes[path]) {
      throw new Error(`Path ${path} does not exists`);
    }

    args.shift();

    return this.route(routes[path]).apply(this, args);
  },

  route(route, ...args) {
    return () => {
      let i = 0;
      const params = [];
      for (i; args.length > i; i++) {
        if (args[i]) {
          params[i] = args[i];
        }
      }

      const s = params.reduce((t, e, n) => t.replace(`:${(n + 1)}`, e), route);

      if (s.indexOf(':') > -1) {
        throw new Error(`Path '${s}' is expecting more arguments`);
      }

      const prefix = (window.DEBUG) ? window.DEBUG : '';

      return prefix + s;
    };
  },
};
