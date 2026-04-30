import DefaultTheme from "vitepress/theme";
import Playground from "./components/Playground.vue";

import type { App } from "vue";

export default {
  ...DefaultTheme,
  enhanceApp({ app }: { app: App }) {
    app.component("Playground", Playground);
  },
};
