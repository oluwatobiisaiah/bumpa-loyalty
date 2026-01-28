#!/bin/bash

# Quick Fix for React 18 Type Issues
# Run this script from your project root

echo "🔧 Fixing React 18 TypeScript Issues..."
echo ""

# Step 1: Clean everything
echo "Step 1: Cleaning all node_modules..."
rm -rf node_modules
rm -rf apps/customer/node_modules
rm -rf apps/admin/node_modules
rm -rf packages/ui/node_modules
rm -rf packages/types/node_modules
rm -rf packages/api-client/node_modules
rm -rf pnpm-lock.yaml
echo "✅ Cleaned!"
echo ""

# Step 2: Update root package.json with overrides
echo "Step 2: Adding React version overrides..."
cat > package.json << 'EOF'
{
  "name": "loyalty-program-monorepo",
  "private": true,
  "version": "1.0.0",
  "workspaces": [
    "apps/*",
    "packages/*"
  ],
  "pnpm": {
    "overrides": {
      "react": "^18.2.0",
      "react-dom": "^18.2.0",
      "@types/react": "^18.2.48",
      "@types/react-dom": "^18.2.18"
    }
  },
  "scripts": {
    "dev": "turbo run dev",
    "dev:customer": "turbo run dev --filter=customer",
    "dev:admin": "turbo run dev --filter=admin",
    "build": "turbo run build",
    "build:customer": "turbo run build --filter=customer",
    "build:admin": "turbo run build --filter=admin",
    "lint": "turbo run lint",
    "test": "turbo run test",
    "type-check": "turbo run type-check",
    "clean": "turbo run clean && rm -rf node_modules"
  },
  "devDependencies": {
    "@turbo/gen": "^2.3.3",
    "turbo": "^2.3.3",
    "prettier": "^3.4.2",
    "typescript": "^5.3.3"
  }
}
EOF
echo "✅ Updated root package.json!"
echo ""

# Step 3: Update tsconfig for packages/ui
echo "Step 3: Fixing tsconfig files..."
cat > packages/ui/tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "useDefineForClassFields": true,
    "lib": ["ES2020", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "jsx": "react-jsx",
    "strict": true,
    "noUnusedLocals": false,
    "noUnusedParameters": false,
    "noFallthroughCasesInSwitch": true
  },
  "include": ["src"],
  "exclude": ["node_modules", "dist"]
}
EOF

cat > packages/types/tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["ES2020"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "strict": true,
    "esModuleInterop": true
  },
  "include": ["src"],
  "exclude": ["node_modules", "dist"]
}
EOF

cat > packages/api-client/tsconfig.json << 'EOF'
{
  "compilerOptions": {
    "target": "ES2020",
    "lib": ["ES2020"],
    "module": "ESNext",
    "skipLibCheck": true,
    "moduleResolution": "bundler",
    "allowImportingTsExtensions": true,
    "resolveJsonModule": true,
    "isolatedModules": true,
    "noEmit": true,
    "strict": true,
    "esModuleInterop": true
  },
  "include": ["src"],
  "exclude": ["node_modules", "dist"]
}
EOF

echo "✅ Updated all tsconfig files!"
echo ""

# Step 4: Fresh install
echo "Step 4: Installing dependencies with forced React version..."
pnpm install
echo ""

# Step 5: Verify
echo "Step 5: Verifying React versions..."
echo "React version:"
pnpm list react | head -10
echo ""
echo "@types/react version:"
pnpm list @types/react | head -10
echo ""

echo "✅ All done!"
echo ""
echo "Now run: pnpm dev"
echo ""