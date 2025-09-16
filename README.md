# Collection Editor

A simple front-end editor for managing products and site content with Firebase (Firestore). Pure HTML/CSS/JS—no build step required.

## Features
- Password gate (client-side; replace before use)
- Real-time Firestore sync with `onSnapshot`
- Sidebar navigation: Dashboard, Add Product, Settings
- Create, edit, delete products
- Categories: watches (`uhr`), vehicles (`fahrzeug`), accessories (`zubehoer`)
- Image and video preview, URL input, optional PHP upload endpoint
- Site settings (contact, WhatsApp messages)
- “Philosophy” page content (title, text, image)

## Tech Stack
- HTML and vanilla ES Modules
- CSS utilities (`style.css` or `style.purged.css`)
- Firebase v10 (CDN): Auth (anonymous) and Firestore

## Project Structure
