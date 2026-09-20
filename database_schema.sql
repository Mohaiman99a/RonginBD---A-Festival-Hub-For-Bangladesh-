-- RonginBD relational schema. User_ID is used as the login ID because the schema has no separate UserName column.
CREATE DATABASE IF NOT EXISTS ronginbd CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE ronginbd;

CREATE TABLE `User` (
    User_ID VARCHAR(50) PRIMARY KEY,
    First_Name VARCHAR(50) NOT NULL,
    Middle_Name VARCHAR(50) NULL,
    Last_Name VARCHAR(50) NOT NULL,
    Password VARCHAR(255) NOT NULL
);

CREATE TABLE Regular (User_ID VARCHAR(50) PRIMARY KEY, FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE);
CREATE TABLE Admin (User_ID VARCHAR(50) PRIMARY KEY, FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE);
CREATE TABLE User_Follow (
    Follower_ID VARCHAR(50), Following_ID VARCHAR(50), PRIMARY KEY (Follower_ID, Following_ID),
    FOREIGN KEY (Follower_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE,
    FOREIGN KEY (Following_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE
);

CREATE TABLE Festival (
    Festival_ID INT AUTO_INCREMENT PRIMARY KEY, Name VARCHAR(120) NOT NULL, Category VARCHAR(50) NOT NULL,
    Date DATE NOT NULL, Image VARCHAR(255) NULL, Description TEXT NOT NULL, AdminUser_ID VARCHAR(50) NOT NULL,
    FOREIGN KEY (AdminUser_ID) REFERENCES Admin(User_ID)
);
CREATE TABLE Post (
    Post_ID INT AUTO_INCREMENT PRIMARY KEY, Title VARCHAR(150) NOT NULL, Image VARCHAR(255) NULL,
    Description TEXT NOT NULL, User_ID VARCHAR(50) NOT NULL, FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE
);
CREATE TABLE Event (
    Post_ID INT PRIMARY KEY, Title VARCHAR(150) NOT NULL, Description TEXT NOT NULL, Time TIME NOT NULL, Date DATE NOT NULL,
    Location VARCHAR(150) NOT NULL, Festival_ID INT NOT NULL, FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE,
    FOREIGN KEY (Festival_ID) REFERENCES Festival(Festival_ID) ON DELETE CASCADE
);
CREATE TABLE Promote (
    Post_ID INT PRIMARY KEY, Link VARCHAR(255) NULL, Cost DECIMAL(10,2) NULL, Phone VARCHAR(30) NULL,
    FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE
);
CREATE TABLE Traditions (Post_ID INT PRIMARY KEY, Festival_ID INT NOT NULL, FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE, FOREIGN KEY (Festival_ID) REFERENCES Festival(Festival_ID) ON DELETE CASCADE);
CREATE TABLE Travel (
    Post_ID INT PRIMARY KEY, FromLocation VARCHAR(100) NOT NULL, ToLocation VARCHAR(100) NOT NULL, Date DATE NOT NULL, Time TIME NOT NULL,
    FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE
);
CREATE TABLE Shopping (Post_ID INT PRIMARY KEY, Category VARCHAR(80) NOT NULL, FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE);
CREATE TABLE Recipe (Post_ID INT PRIMARY KEY, Recipe TEXT NOT NULL, FOREIGN KEY (Post_ID) REFERENCES Post(Post_ID) ON DELETE CASCADE);
CREATE TABLE Recipe_Ingredients (Post_ID INT PRIMARY KEY, Ingredients TEXT NOT NULL, FOREIGN KEY (Post_ID) REFERENCES Recipe(Post_ID) ON DELETE CASCADE);

CREATE TABLE Album (
    Album_ID INT AUTO_INCREMENT PRIMARY KEY, Title VARCHAR(120) NOT NULL, Privacy VARCHAR(20) NOT NULL, User_ID VARCHAR(50) NOT NULL,
    FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE
);
CREATE TABLE Album_Access (
    User_ID VARCHAR(50), Album_ID INT, PRIMARY KEY (User_ID, Album_ID),
    FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE, FOREIGN KEY (Album_ID) REFERENCES Album(Album_ID) ON DELETE CASCADE
);
CREATE TABLE Photos (Photo_ID INT AUTO_INCREMENT PRIMARY KEY, Album_ID INT NOT NULL, Image VARCHAR(255) NOT NULL, FOREIGN KEY (Album_ID) REFERENCES Album(Album_ID) ON DELETE CASCADE);
CREATE TABLE Bookmark (
    Bookmark_ID INT AUTO_INCREMENT PRIMARY KEY, ItemType VARCHAR(30) NOT NULL, ItemID INT NOT NULL, User_ID VARCHAR(50) NOT NULL,
    FOREIGN KEY (User_ID) REFERENCES `User`(User_ID) ON DELETE CASCADE
);

-- One user can save a content item only once; this preserves user-specific bookmark state.
CREATE UNIQUE INDEX idx_bookmark_user_item ON Bookmark (User_ID, ItemType, ItemID);

-- Additional B-tree index for the Festival Calendar category filter and date ordering.
CREATE INDEX idx_festival_category_date ON Festival (Category, Date);

-- Development/demo accounts. Passwords were produced with PHP password_hash().
INSERT INTO `User` (User_ID, First_Name, Middle_Name, Last_Name, Password) VALUES
('admin_demo', 'Demo', NULL, 'Admin', '$2y$10$Ej6YYjxfZ5MknqngyFrGgeISkeHXjlAYPmITBA3JIMjkhPvrI2qJG'),
('user_demo', 'Demo', NULL, 'User', '$2y$10$ft5Qh7RFUYtimLgioqDkTu7kaXXhsvkhrjLEeegIjg8hk3Kd1VAGK');
INSERT INTO Admin (User_ID) VALUES ('admin_demo');
INSERT INTO Regular (User_ID) VALUES ('user_demo');
INSERT INTO Festival (Name, Category, Date, Image, Description, AdminUser_ID) VALUES
('Pohela Boishakh', 'Culture Based', '2026-04-14', '', 'Celebrate the Bengali New Year with music, food, and colourful fairs.', 'admin_demo');
