# PMS Shared Assets - Fonts

This directory contains shared fonts for the PMS system.

## Font Files:

### Primary Fonts
- `Inter-Regular.woff2` - Inter font family (regular)
- `Inter-Medium.woff2` - Inter font family (medium)
- `Inter-SemiBold.woff2` - Inter font family (semi-bold)
- `Inter-Bold.woff2` - Inter font family (bold)

### Icon Fonts
- `FontAwesome.woff2` - FontAwesome icon font
- `MaterialIcons.woff2` - Material Design icons

## Font Loading:
```css
@font-face {
    font-family: 'Inter';
    src: url('/seait/pms/assets/fonts/Inter-Regular.woff2') format('woff2');
    font-weight: 400;
    font-style: normal;
    font-display: swap;
}
```

## Usage:
```css
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}
```

## Font Optimization:
- WOFF2 format for modern browsers
- Font-display: swap for better loading performance
- Subset fonts to include only used characters
- Preload critical fonts in HTML head
