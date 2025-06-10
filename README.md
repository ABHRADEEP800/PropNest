
# 🏠 Real Estate Manager Plugin & Theme

**Supercharge your WordPress site with a complete property management system!** This plugin & theme handles properties, leads, and contact forms with modern frontend displays.

## ✨ Features
- **🏢 Property Management** (CRUD operations)
- **📊 Leads System** with "Book Now" modals
- **📝 Contact Form Entries**
- **💻 Admin Dashboard** for all operations
- **🎨 Responsive Shortcodes** with Bootstrap 5 styling
- **🔍 Search & Pagination**
- **📱 Mobile-Friendly Design**

## ⚙️ Installation
1. Upload `real-estate-manager` & `real-estate-theme` Folders via WordPress plugins & themes page (Before Upload Zip The Folders)
2. Activate the plugin & theme
3. Database tables will auto-create on activation

## 🛠 Admin Usage
Navigate to: **Dashboard → Real Estate Manager**

| Menu Item | Functionality |
|-----------|---------------|
| 🏠 Properties | View/delete properties |
| ➕ Add New Property | Add new property listing |
| ✏️ Edit Property | Modify existing properties |
| 📋 Leads | View all lead submissions |
| ✉️ Contact Entries | See contact form data |

## 🔌 Shortcodes

### Property Grid
```php
[rmedb_properties limit="6"]
```
- Displays property cards with search and pagination
- `limit` parameter controls items per page
- Includes "Book Now" modals

### Single Property View
```php
[rmedb_property]
```
- Shows detailed property view
- Requires URL parameter: `?property_id=123`
- Includes dedicated booking modal
  
## 🌐 Live Demo
👉 [https://propnest.abhradeep.com](https://propnest.abhradeep.com)

## 🚀 Roadmap
- [ ] Agent management system
- [ ] Property comparison tool
- [ ] PDF brochure generator
- [ ] Google Maps integration
- [ ] Appointment scheduler

## 💖 Support
**Created with ❤️ by Abhradeep Biswas**  
🌐 [https://abhradeep.com](https://abhradeep.com)  
📧 hello@abhradeep.com
