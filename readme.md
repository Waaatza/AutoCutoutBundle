# Watza AutoCutoutBundle

**Work-In-Progress**

A Pimcore 11 bundle that provides automatic image background removal for assets in the `Awards` folder.  
The bundle adds a button to the Pimcore asset toolbar, which removes the background of an image and saves the result in a `_freigestellt` subfolder.

---

## Features

- Toolbar button in the Pimcore Asset Detail view:
    - **Save & Publish** → keeps the standard Pimcore save functionality
    - **AutoCutout** → triggers automatic background removal
- Stores a **custom property `cutout_fuzz`** to control the tolerance for background removal
- Automatically creates the `_freigestellt` target folder under `/Awards` if it doesn't exist
- Uses a flood-fill algorithm on the four corners of the image to safely remove the background
- Output is a PNG with transparent background