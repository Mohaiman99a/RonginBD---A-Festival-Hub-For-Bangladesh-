
CREATE INDEX idx_festival_category_date
ON Festival (Category, Date);


CREATE UNIQUE INDEX idx_bookmark_user_item
ON Bookmark (User_ID, ItemType, ItemID);
