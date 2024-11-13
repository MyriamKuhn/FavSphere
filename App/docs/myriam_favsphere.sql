CREATE TABLE user (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  user VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE category (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL,
  fk_user_id INT(11) UNSIGNED NOT NULL,
  FOREIGN KEY(fk_user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE link (
  id INT(11) UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  url VARCHAR(255) NOT NULL,
  title VARCHAR(100) NOT NULL,
  description TEXT NOT NULL,
  fk_category_id INT(11) UNSIGNED NOT NULL,
  fk_user_id INT(11) UNSIGNED NOT NULL,
  FOREIGN KEY(fk_category_id) REFERENCES category(id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY(fk_user_id) REFERENCES user(id) ON DELETE CASCADE ON UPDATE CASCADE
) 
ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;