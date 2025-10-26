# PWA Icons Directory

## Quick Fix (Already Applied)
The manifest.json has been updated to use the existing `favicon.ico` file, so the 404 errors should be resolved immediately.

## Optional: Generate Custom Icons

If you want custom PWA icons for better mobile experience:

### Method 1: Use the HTML Generator (Easiest)
1. Open `generate-icons.html` in your browser
2. Icons will auto-generate
3. Click "Download All Icons as ZIP" or download individually
4. Save all downloaded PNG files to this `/public/icons/` directory
5. Update `manifest.json` to reference these new icons

### Method 2: Use Online Tool
1. Go to https://www.pwabuilder.com/imageGenerator or https://realfavicongenerator.net/
2. Upload your logo/icon (512x512 PNG recommended)
3. Download the generated icon pack
4. Extract all icons to this directory

### Method 3: Manual Creation
Create PNG files with these sizes:
- icon-16x16.png
- icon-32x32.png
- icon-72x72.png
- icon-96x96.png
- icon-128x128.png
- icon-144x144.png
- icon-152x152.png
- icon-192x192.png
- icon-384x384.png
- icon-512x512.png

## After Adding Icons

Update `public/manifest.json` to reference your new icons:

```json
{
  "icons": [
    {
      "src": "/icons/icon-72x72.png",
      "sizes": "72x72",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-96x96.png",
      "sizes": "96x96",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-144x144.png",
      "sizes": "144x144",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-192x192.png",
      "sizes": "192x192",
      "type": "image/png"
    },
    {
      "src": "/icons/icon-512x512.png",
      "sizes": "512x512",
      "type": "image/png"
    }
  ]
}
```

## Current Status
✅ **FIXED**: The app now uses `favicon.ico` as fallback, no more 404 errors!
⏳ **OPTIONAL**: You can add custom icons later for better PWA experience

