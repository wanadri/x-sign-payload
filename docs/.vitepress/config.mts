import { defineConfig } from "vitepress";
import { tabsMarkdownPlugin } from "vitepress-plugin-tabs";

export default defineConfig({
  markdown: {
    config: (md) => {
      md.use(tabsMarkdownPlugin);
    },
  },
  title: "x-sign-payload",
  description: "Secure Request Signing Made Simple",
  base: "/x-sign-payload/",
  themeConfig: {
    nav: [
      { text: "Home", link: "/" },
      { text: "Documentation", link: "/introduction" },
      { text: "GitHub", link: "https://github.com/wanadri/x-sign-payload" },
    ],

    sidebar: [
      {
        text: "Overview",
        items: [
          { text: "Introduction", link: "/introduction" },
          { text: "Support Me", link: "/support-me" },
          { text: "Changelog", link: "/changelog" },
        ],
      },
      {
        text: "Get Started",
        collapsed: false,
        items: [
          { text: "Installation", link: "/get-started/installation" },
          { text: "Quick Setup", link: "/get-started/quick-setup" },
          { text: "How to Use", link: "/get-started/how-to-use" },
          {
            text: "Middleware Implementation",
            link: "/get-started/middleware-implementation",
          },
        ],
      },
    ],

    socialLinks: [
      { icon: "github", link: "https://github.com/wanadri/x-sign-payload" },
    ],

    footer: {
      message: "Released under the MIT License.",
      copyright: "Copyright © 2024 wanadri",
    },
  },
});
