# AutoCutoutBundle for Pimcore 11

**Work-In-Progress**

The Watza AutoCutoutBundle provides automatic image single-colored-background removal for assets located in a `/Awards` folder.  
The bundle adds a button to the Pimcore asset toolbar to remove the background of an image and save the result in a `_freigestellt` subfolder.
The first removal is done automatically by an EventListener.

---

## Features

- **Toolbar Integration:** Adds a **“Freistellen”** button in the Pimcore Asset Detail view for images located in `/Awards`.
- **Custom Property:** Stores a `cutout_fuzz` property on the original asset to control background removal tolerance.
- **Automatic Folder Creation:** Creates the `_freigestellt` target folder under `/Awards` if it doesn't already exist.
- **Background Removal Algorithm:** Uses a flood-fill method on the four corners of the image to safely remove the background.
- **Output Format:** Generates a PNG with a transparent background.
- **Preview & Fuzz Control:** Provides a live preview with a slider to adjust fuzziness before finalizing the image.
- **Notifications:** Shows a short success notification in Pimcore after the background removal completes.

---

## Usage

1. Open an image asset in the `/Awards` folder.
2. Click the **Freistellen** button in the asset toolbar.
3. Adjust the **Fuzz (%)** slider in the preview window to fine-tune background removal.
4. Click **Final speichern** to save the image with a transparent background.  
   The new image is stored in the `_freigestellt` subfolder, and a short notification confirms success.

---

## Notes

- Works **only on image assets**.
- The bundle is still a **work in progress** and may be extended with additional options in the future. Use at your own risk!