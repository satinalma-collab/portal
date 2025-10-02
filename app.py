import os
from flask import Flask, render_template, request, redirect, url_for, session, flash, g
from modules.encryption_manager import load_data, save_data, generate_initial_data
import bcrypt
from functools import wraps

app = Flask(__name__)
# Secret key for session management. In a real app, use a more secure, randomly generated key.
app.secret_key = os.environ.get("FLASK_SECRET_KEY", "a_very_secret_key_for_development")

# Generate initial data if it doesn't exist
generate_initial_data()

# --- DECORATORS ---
def login_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user' not in session:
            flash("You need to be logged in to view this page.", "warning")
            return redirect(url_for('login'))
        return f(*args, **kwargs)
    return decorated_function

def admin_required(f):
    @wraps(f)
    def decorated_function(*args, **kwargs):
        if 'user' not in session or session['user'].get('role') != 'admin':
            flash("You do not have permission to access this page.", "danger")
            return redirect(url_for('index'))
        return f(*args, **kwargs)
    return decorated_function

@app.before_request
def before_request():
    g.user = session.get('user')
    if g.user:
        data = load_data()
        g.menus = data.get('menus', [])
    else:
        g.menus = []


# --- AUTHENTICATION ROUTES ---
@app.route('/login', methods=['GET', 'POST'])
def login():
    if 'user' in session:
        return redirect(url_for('index'))

    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password'].encode('utf-8')

        data = load_data()
        user = next((u for u in data['users'] if u['username'] == username), None)

        if user and bcrypt.checkpw(password, user['passwordHash'].encode('utf-8')):
            session['user'] = user
            flash(f'Welcome back, {user["username"]}!', 'success')

            # Check if admin is using default password
            if user['username'] == 'admin' and bcrypt.checkpw(b'admin', user['passwordHash'].encode('utf-8')):
                flash("Warning: You are using the default admin password. Please change it in the admin panel.", "warning")

            return redirect(url_for('index'))
        else:
            flash('Invalid username or password. Please try again.', 'danger')
            return redirect(url_for('login'))

    return render_template('login.html')

@app.route('/logout')
def logout():
    session.pop('user', None)
    flash('You have been logged out.', 'info')
    return redirect(url_for('login'))


# --- CORE APPLICATION ROUTES ---
@app.route('/')
@login_required
def index():
    data = load_data()
    user_permissions = session['user']['permissions']

    # Filter applications based on user permissions
    if "all" in user_permissions:
        visible_apps = data['applications']
    else:
        visible_apps = [app for app in data['applications'] if app['id'] in user_permissions]

    return render_template('index.html', applications=visible_apps)


# --- ADMIN PANEL ROUTES ---
@app.route('/admin')
@login_required
@admin_required
def admin_dashboard():
    return render_template('admin/dashboard.html')

@app.route('/admin/users')
@login_required
@admin_required
def admin_users():
    data = load_data()
    return render_template('admin/users.html', users=data['users'])

@app.route('/admin/users/add', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_add_user():
    data = load_data()
    if request.method == 'POST':
        username = request.form['username']
        password = request.form['password'].encode('utf-8')
        role = request.form['role']
        permissions = request.form.getlist('permissions')

        if any(u['username'] == username for u in data['users']):
            flash(f"Username '{username}' already exists.", 'danger')
            return render_template('admin/user_form.html', user=request.form, applications=data['applications'])

        hashed_password = bcrypt.hashpw(password, bcrypt.gensalt()).decode('utf-8')
        new_id = max([u['id'] for u in data['users']] or [0]) + 1

        new_user = {
            "id": new_id,
            "username": username,
            "passwordHash": hashed_password,
            "role": role,
            "permissions": ["all"] if "all" in permissions else [int(p) for p in permissions]
        }

        data['users'].append(new_user)
        save_data(data)
        flash(f"User '{username}' created successfully!", 'success')
        return redirect(url_for('admin_users'))

    return render_template('admin/user_form.html', user=None, applications=data['applications'])

@app.route('/admin/users/edit/<int:user_id>', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_edit_user(user_id):
    data = load_data()
    user = next((u for u in data['users'] if u['id'] == user_id), None)
    if not user:
        flash("User not found.", "danger")
        return redirect(url_for('admin_users'))

    if request.method == 'POST':
        user['username'] = request.form['username']
        user['role'] = request.form['role']
        permissions = request.form.getlist('permissions')
        user['permissions'] = ["all"] if "all" in permissions else [int(p) for p in permissions]

        if request.form['password']:
            password = request.form['password'].encode('utf-8')
            user['passwordHash'] = bcrypt.hashpw(password, bcrypt.gensalt()).decode('utf-8')

        save_data(data)
        flash(f"User '{user['username']}' updated successfully!", 'success')
        return redirect(url_for('admin_users'))

    return render_template('admin/user_form.html', user=user, applications=data['applications'])

@app.route('/admin/users/delete/<int:user_id>', methods=['POST'])
@login_required
@admin_required
def admin_delete_user(user_id):
    if user_id == session['user']['id']:
        flash("You cannot delete your own account.", 'danger')
        return redirect(url_for('admin_users'))

    data = load_data()
    user = next((u for u in data['users'] if u['id'] == user_id), None)

    if user:
        data['users'] = [u for u in data['users'] if u['id'] != user_id]
        save_data(data)
        flash(f"User '{user['username']}' has been deleted.", 'success')
    else:
        flash("User not found.", 'danger')

    return redirect(url_for('admin_users'))


# --- APPLICATION MANAGEMENT ROUTES ---
@app.route('/admin/applications')
@login_required
@admin_required
def admin_applications():
    data = load_data()
    return render_template('admin/applications.html', applications=data.get('applications', []))

@app.route('/admin/applications/add', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_add_application():
    if request.method == 'POST':
        data = load_data()
        new_id = max([a['id'] for a in data.get('applications', [])] or [100]) + 1

        new_app = {
            "id": new_id,
            "name": request.form['name'],
            "description": request.form['description'],
            "path": request.form['path'],
            "icon": request.form['icon']
        }

        data.setdefault('applications', []).append(new_app)
        save_data(data)
        flash(f"Application '{new_app['name']}' created successfully!", 'success')
        return redirect(url_for('admin_applications'))

    return render_template('admin/application_form.html', app=None)

@app.route('/admin/applications/edit/<int:app_id>', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_edit_application(app_id):
    data = load_data()
    app = next((a for a in data.get('applications', []) if a['id'] == app_id), None)
    if not app:
        flash("Application not found.", "danger")
        return redirect(url_for('admin_applications'))

    if request.method == 'POST':
        app['name'] = request.form['name']
        app['description'] = request.form['description']
        app['path'] = request.form['path']
        app['icon'] = request.form['icon']

        save_data(data)
        flash(f"Application '{app['name']}' updated successfully!", 'success')
        return redirect(url_for('admin_applications'))

    return render_template('admin/application_form.html', app=app)

@app.route('/admin/applications/delete/<int:app_id>', methods=['POST'])
@login_required
@admin_required
def admin_delete_application(app_id):
    data = load_data()
    app = next((a for a in data.get('applications', []) if a['id'] == app_id), None)

    if app:
        # Remove the application
        data['applications'] = [a for a in data['applications'] if a['id'] != app_id]
        # Revoke permission from all users
        for user in data['users']:
            if "all" not in user['permissions'] and app_id in user['permissions']:
                user['permissions'].remove(app_id)

        save_data(data)
        flash(f"Application '{app['name']}' and all associated permissions have been deleted.", 'success')
    else:
        flash("Application not found.", 'danger')

    return redirect(url_for('admin_applications'))


# --- MENU MANAGEMENT ROUTES ---
@app.route('/admin/menus')
@login_required
@admin_required
def admin_menus():
    data = load_data()
    return render_template('admin/menus.html', menus=data.get('menus', []))

@app.route('/admin/menus/add', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_add_menu():
    if request.method == 'POST':
        data = load_data()
        new_id = max([m['id'] for m in data.get('menus', [])] or [0]) + 1

        new_menu_item = {
            "id": new_id,
            "title": request.form['title'],
            "path": request.form['path'],
            "order": int(request.form['order'])
        }

        data.setdefault('menus', []).append(new_menu_item)
        save_data(data)
        flash(f"Menu item '{new_menu_item['title']}' created successfully!", 'success')
        return redirect(url_for('admin_menus'))

    return render_template('admin/menu_form.html', menu=None)

@app.route('/admin/menus/edit/<int:menu_id>', methods=['GET', 'POST'])
@login_required
@admin_required
def admin_edit_menu(menu_id):
    data = load_data()
    menu = next((m for m in data.get('menus', []) if m['id'] == menu_id), None)
    if not menu:
        flash("Menu item not found.", "danger")
        return redirect(url_for('admin_menus'))

    if request.method == 'POST':
        menu['title'] = request.form['title']
        menu['path'] = request.form['path']
        menu['order'] = int(request.form['order'])

        save_data(data)
        flash(f"Menu item '{menu['title']}' updated successfully!", 'success')
        return redirect(url_for('admin_menus'))

    return render_template('admin/menu_form.html', menu=menu)

@app.route('/admin/menus/delete/<int:menu_id>', methods=['POST'])
@login_required
@admin_required
def admin_delete_menu(menu_id):
    data = load_data()
    menu = next((m for m in data.get('menus', []) if m['id'] == menu_id), None)

    if menu:
        data['menus'] = [m for m in data['menus'] if m['id'] != menu_id]
        save_data(data)
        flash(f"Menu item '{menu['title']}' has been deleted.", 'success')
    else:
        flash("Menu item not found.", 'danger')

    return redirect(url_for('admin_menus'))


if __name__ == '__main__':
    # For development, you might want to run in debug mode.
    # Ensure the environment variable for the secret key is set before running in production.
    if not os.environ.get("PORTAL_CORE_SECRET_KEY"):
        print("Warning: PORTAL_CORE_SECRET_KEY is not set. Using a default key.")
        print("For production, set a strong, unique key as an environment variable.")

    app.run(debug=True, port=5001)