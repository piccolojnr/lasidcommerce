# Backthred category image pack — 81 categories

This pack contains **81 optimized category images**:

- **23 images reused** from the original Backthred category pack
- **58 newly sourced images** for the expanded category catalogue
- Every image is **1600 × 1200 px** with a consistent **4:3 aspect ratio**
- Images use JPEG compression suitable for web delivery
- Filenames match the recommended category slugs

## Folder contents

- `images/` — all 81 optimized category images
- `manifest.json` — category metadata, hierarchy, filenames, and source URLs
- `SOURCES.md` — Pexels source page for every image
- `PREVIEW.jpg` — contact sheet for reviewing the full pack

## Duplicate Outerwear labels

Your database includes two categories named `Outerwear`, one under Women's Fashion and one under Men's Fashion. They are disambiguated in this pack as:

- `womens-outerwear.jpg`
- `mens-outerwear.jpg`

The manifest retains `database_label: "Outerwear"` and includes the relevant parent/context.

## Frontend usage

```tsx
<Image
  src={`/categories/${category.slug}.jpg`}
  alt={category.name}
  width={1600}
  height={1200}
  className="h-full w-full object-cover"
/>
```

Keep category names and promotional text in HTML rather than baking text into the images.
