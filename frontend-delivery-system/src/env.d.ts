/// <reference types="vite/client" />

// Makes TypeScript understand `.vue` SFC imports in the editor.
declare module '*.vue' {
  import type { DefineComponent } from 'vue'
  const component: DefineComponent<{}, {}, any>
  export default component
}

