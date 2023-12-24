Welcome to FriendPix-Social-Album-Sharing, a web application that allows users to share images and albums with their friends. Users can create albums, upload pictures, connect with others, and manage their content with enhanced privacy controls.

Features
User Authentication: Secure sign-in and login functionality.
Album and Image Sharing: Users can create albums, upload images, and share them with friends.
Comment System: Friends can write comments on shared images.
Friendship System: Establish connections with other users for sharing content.

Database Structure
The web application utilizes a MySQL database. Below are the SQL scripts to set up the necessary tables:

User
 UserId varchar(16) NOT NULL PRIMARY KEY,
 Name varchar(256) NOT NULL,
 Phone varchar(16),
 Password varchar(256)

 Accessibility
 Accessibility_Code varchar(16) NOT NULL PRIMARY KEY,
 Description varchar(127) NOT NULL 

 Album
 Album_Id int(11) PRIMARY KEY AUTO_INCREMENT,
 Title varchar(256) NOT NULL,
 Description varchar(3000),
 Owner_Id varchar(16) NOT NULL,
 Accessibility_Code varchar(16) NOT NULL,
FOREIGN KEY (Owner_Id) REFERENCES User (UserId),
 FOREIGN KEY (Accessibility_Code) REFERENCES Accessibility (Accessibility_Code) 

Picture
 Picture_Id, int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 Album_Id, int(11), NOT NULL,
 FileName, varchar(255) NOT NULL,
 Title, varchar(256) NOT NULL,
 Description, varchar(3000),
FOREIGN KEY (Album_Id) REFERENCES Album (Album_Id)

Comment
Comment_Id int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
 Author_Id varchar(16) NOT NULL,
Picture_Id int(11) NOT NULL,
Comment_Text varchar(3000) NOT NULL,
FOREIGN KEY (Author_Id) REFERENCES User (UserId),
FOREIGN KEY (Picture _Id) REFERENCES Picture (Picture _Id)

FriendshipStatus
Status_Code varchar(16) NOT NULL PRIMARY KEY,
Description varchar(128) NOT NULL,

Friendship
Friend_RequesterId varchar(16) NOT NULL PRIMARY KEY,


Configuration
To configure the application for your MySQL database, update the configuration file located at Project.ini .
Friend_RequesteeId varchar(16) NOT NULL PRIMARY KEY,
Status varchar(16) NOT NULL,
FOREIGN KEY (Friend_RequesterId) REFERENCES User (UserId),
FOREIGN KEY (Friend_RequesteeId) REFERENCES User (UserId),
FOREIGN KEY (Status) REFERENCES FriendshipStatus (Status_Code)
