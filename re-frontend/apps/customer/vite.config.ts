import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'
import path from 'path'

// https://vite.dev/config/
export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: {
      '@loyalty/ui': path.resolve(__dirname, '../../packages/ui/src'),
      '@loyalty/api-client': path.resolve(__dirname, '../../packages/api-client/src'),
      '@loyalty/types': path.resolve(__dirname, '../../packages/types/src'),
    },
  },
})
