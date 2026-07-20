# Backthred category assets

- `images/` contains 23 optimized JPEG category images.
- Every image is 1600 × 1200 pixels with a 4:3 aspect ratio.
- Filenames match the category slugs.
- `SOURCES.md` records each Pexels source and the key license cautions.

Recommended frontend usage:

```tsx
<Image
  src="/categories/womens-fashion.jpg"
  alt="Women's Fashion"
  width={1600}
  height={1200}
  className="h-full w-full object-cover"
/>
```

Keep category labels in HTML rather than baking text into the images.
