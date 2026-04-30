import DefaultTheme from "vitepress/theme";
import { enhanceAppWithTabs } from "vitepress-plugin-tabs/client";
import Playground from "./components/Playground.vue";

import type { App } from "vue";

export default {
  ...DefaultTheme,
  enhanceApp({ app }: { app: App }) {
    enhanceAppWithTabs(app);
    app.component("Playground", Playground);
  },
};
