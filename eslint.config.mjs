import globals from "globals";
import pluginJs from "@eslint/js";

export default [
  {
    files: ["assets/**/*.js"],
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.jquery,
      }
    }
  },
  pluginJs.configs.recommended,
];
