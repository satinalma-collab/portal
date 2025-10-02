import os
import json
from cryptography.fernet import Fernet, InvalidToken
from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC
from cryptography.hazmat.primitives import hashes
from cryptography.hazmat.backends import default_backend
import base64

DATA_FILE = "data.json.enc"
SALT = b'salt_' # In a real app, this should be unique and stored securely

def get_key_from_password(password, salt):
    kdf = PBKDF2HMAC(
        algorithm=hashes.SHA256(),
        length=32,
        salt=salt,
        iterations=100000,
        backend=default_backend()
    )
    return base64.urlsafe_b64encode(kdf.derive(password.encode()))

def get_secret_key():
    """
    Retrieves the secret key from an environment variable.
    If not set, it generates a new key.
    """
    secret_key = os.environ.get("PORTAL_CORE_SECRET_KEY")
    if not secret_key:
        print("Warning: PORTAL_CORE_SECRET_KEY environment variable not set. Using a default key.")
        # In a real scenario, you should enforce setting this variable.
        secret_key = "default_secret_key_for_development"

    # Use a KDF to derive a key of the correct length for Fernet
    return get_key_from_password(secret_key, SALT)

fernet = Fernet(get_secret_key())

def load_data():
    """
    Reads the encrypted data file, decrypts it, and returns the JSON data.
    If the file doesn't exist, it returns a default structure.
    """
    if not os.path.exists(DATA_FILE):
        return {"users": [], "applications": [], "menus": []}

    try:
        with open(DATA_FILE, "rb") as f:
            encrypted_data = f.read()

        decrypted_data = fernet.decrypt(encrypted_data)
        return json.loads(decrypted_data.decode('utf-8'))
    except (InvalidToken, FileNotFoundError):
        # If decryption fails or file not found, return default structure
        return {"users": [], "applications": [], "menus": []}
    except Exception as e:
        print(f"An error occurred while loading data: {e}")
        # In a real app, you might want to handle this more gracefully
        # For example, by restoring from a backup.
        return None


def save_data(data):
    """

    Encrypts the given JSON data and writes it to the data file.
    """
    try:
        json_data = json.dumps(data, indent=2).encode('utf-8')
        encrypted_data = fernet.encrypt(json_data)

        with open(DATA_FILE, "wb") as f:
            f.write(encrypted_data)
        return True
    except Exception as e:
        print(f"An error occurred while saving data: {e}")
        return False

def generate_initial_data():
    """
    Generates the initial data.json file if it doesn't exist.
    """
    import bcrypt

    if os.path.exists(DATA_FILE):
        return

    # Default admin password is 'admin'
    password = b'admin'
    hashed_password = bcrypt.hashpw(password, bcrypt.gensalt()).decode('utf-8')

    initial_data = {
      "users": [
        {
          "id": 1,
          "username": "admin",
          "passwordHash": hashed_password,
          "role": "admin",
          "permissions": ["all"]
        },
        {
          "id": 2,
          "username": "kullanici1",
          "passwordHash": bcrypt.hashpw(b'password123', bcrypt.gensalt()).decode('utf-8'),
          "role": "user",
          "permissions": [101, 103]
        }
      ],
      "applications": [
        {
          "id": 101,
          "name": "Müşteri Yönetim Sistemi",
          "description": "Müşteri bilgilerini takip etme uygulaması.",
          "path": "/crm",
          "icon": "user-tie"
        },
        {
          "id": 102,
          "name": "Proje Takibi",
          "description": "Devam eden projelerin durumunu izleyin.",
          "path": "/projects",
          "icon": "tasks"
        },
        {
          "id": 103,
          "name": "Raporlama Aracı",
          "description": "Satış ve pazarlama raporları oluşturun.",
          "path": "/reports",
          "icon": "chart-pie"
        }
      ],
      "menus": [
        {
          "id": 1,
          "title": "Anasayfa",
          "path": "/",
          "order": 1
        },
        {
          "id": 2,
          "title": "Destek",
          "path": "/support",
          "order": 2
        }
      ]
    }

    save_data(initial_data)
    print(f"Initial data created and saved to {DATA_FILE}")

if __name__ == '__main__':
    # This allows us to generate the initial encrypted file from the command line
    generate_initial_data()