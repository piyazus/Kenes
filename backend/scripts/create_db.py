import pymysql

def reset_database():
    print("Connecting to MySQL server...")
    try:
        connection = pymysql.connect(
            host='localhost',
            user='root',
            password='',
            charset='utf8mb4',
            cursorclass=pymysql.cursors.DictCursor
        )
        
        try:
            with connection.cursor() as cursor:
                # Force reset
                print("Dropping database 'kenes' if exists...")
                cursor.execute("DROP DATABASE IF EXISTS kenes")
                print("Creating database 'kenes'...")
                cursor.execute("CREATE DATABASE kenes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
                print("Database reset successfully!")
        finally:
            connection.close()
            
    except Exception as e:
        print(f"Failed to reset database: {e}")

if __name__ == "__main__":
    reset_database()
