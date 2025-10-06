
# Staff Directory (PHP + MySQL) 

**Features**
- Add/Edit/Delete staff (Name, Designation, Department, Email (optional), Intercom, Direct Number, Address, Blood Group (optional), Category)
- Manage **Departments** and **Categories** (Faculty, Staff, Administrator, Student, Other — add your own)
- Front page listing with search + Department/Category filters
- **Pagination** on public and admin pages
- **CSV import** (header optional, flexible aliases, auto-creates departments/categories)

## Install
1. Create a MySQL DB (e.g., `staff_directory`).
2. Import `schema.sql` into that DB.
3. Configure `config.php` with Host/DB/User/Pass and change the default admin password.
4. Copy this folder to your server, e.g., `C:\xampp\htdocs\staff` or `/var/www/html/staff`.
5. Open `http://localhost/staff/` for public view, and `http://localhost/staff/admin/login.php` for admin.

**Default Admin**: `admin / Admin@123` → change in `config.php`.

## CSV import
- Save your Excel sheet as **CSV (Comma delimited)**.
- Header is optional. If header present, columns can be in any order. Recognized aliases are case-insensitive.
- Without header, positional order supported (7 or 8 columns):
  - 7: `Name,Designation,Department,Email,Intercom,Address,BloodGroup`
  - 8: `Name,Designation,Department,Email,Intercom,DirectNumber,Address,BloodGroup`
- Aliases: `Name`, `Designation`, `Department/Dept`, `Email/Email ID`, `Intercom/Mobile`, `DirectNumber/OfficePhone`, `Address`, `BloodGroup`, `Category/Role/Type`.
- Department blanks become **Unassigned** (auto-created). Category blanks default to **Staff**. **Intercom is required**.
