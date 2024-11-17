import globals from "globals";
import pluginJs from "@eslint/js";

export default [
  {
    files: ["webpack.config.js"],
    languageOptions: {
      globals: {
        ...globals.node,
      }
    }
  },
  {
    files: ["assets/**/*.js"],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.jquery,
      }
    }
  },
  {
    files: ["apps/socket-server/**/*.js"],
    languageOptions: {
      globals: {
        ...globals.node,
      }
    }
  },
  pluginJs.configs.recommended,
];
