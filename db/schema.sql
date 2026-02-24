CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  role ENUM('teacher','student') NOT NULL,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  activation_token VARCHAR(128) NULL,
  password_hash VARCHAR(255) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE `groups` (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  group_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE
);

CREATE TABLE team_members (
  team_id INT NOT NULL,
  user_id INT NOT NULL,
  PRIMARY KEY (team_id,user_id),
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE activity_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  type ENUM('card_sorting','tree_testing') NOT NULL,
  title VARCHAR(190) NOT NULL,
  instructions TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE activity_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  group_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (template_id) REFERENCES activity_templates(id) ON DELETE CASCADE,
  FOREIGN KEY (group_id) REFERENCES `groups`(id) ON DELETE CASCADE
);

CREATE TABLE activity_instances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  template_id INT NOT NULL,
  team_id INT NOT NULL,
  status ENUM('draft','published','closed') NOT NULL DEFAULT 'draft',
  participant_token VARCHAR(128) NOT NULL UNIQUE,
  cs_mode ENUM('open','closed','hybrid') NOT NULL DEFAULT 'open',
  allow_multi_category TINYINT(1) NOT NULL DEFAULT 0,
  max_responses INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (template_id) REFERENCES activity_templates(id) ON DELETE CASCADE,
  FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
);

CREATE TABLE cs_cards (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  label VARCHAR(190) NOT NULL,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE
);

CREATE TABLE cs_seed_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE
);

CREATE TABLE cs_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  alias VARCHAR(120) NOT NULL,
  started_at DATETIME NULL,
  finished_at DATETIME NULL,
  time_spent_ms INT NULL,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE
);

CREATE TABLE cs_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  instance_id INT NOT NULL,
  name VARCHAR(120) NOT NULL,
  FOREIGN KEY (participant_id) REFERENCES cs_participants(id) ON DELETE CASCADE,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE
);

CREATE TABLE cs_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  card_id INT NOT NULL,
  category_id INT NOT NULL,
  FOREIGN KEY (participant_id) REFERENCES cs_participants(id) ON DELETE CASCADE,
  FOREIGN KEY (card_id) REFERENCES cs_cards(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES cs_categories(id) ON DELETE CASCADE
);

CREATE TABLE tt_nodes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  parent_id INT NULL,
  label VARCHAR(190) NOT NULL,
  position INT NOT NULL DEFAULT 0,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE,
  FOREIGN KEY (parent_id) REFERENCES tt_nodes(id) ON DELETE SET NULL
);

CREATE TABLE tt_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  prompt VARCHAR(255) NOT NULL,
  correct_node_id INT NOT NULL,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE,
  FOREIGN KEY (correct_node_id) REFERENCES tt_nodes(id) ON DELETE CASCADE
);

CREATE TABLE tt_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  instance_id INT NOT NULL,
  alias VARCHAR(120) NOT NULL,
  started_at DATETIME NULL,
  finished_at DATETIME NULL,
  time_spent_ms INT NULL,
  FOREIGN KEY (instance_id) REFERENCES activity_instances(id) ON DELETE CASCADE
);

CREATE TABLE tt_responses (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  task_id INT NOT NULL,
  selected_node_id INT NOT NULL,
  path_text TEXT NOT NULL,
  time_spent_ms INT NOT NULL,
  is_correct TINYINT(1) NOT NULL,
  FOREIGN KEY (participant_id) REFERENCES tt_participants(id) ON DELETE CASCADE,
  FOREIGN KEY (task_id) REFERENCES tt_tasks(id) ON DELETE CASCADE,
  FOREIGN KEY (selected_node_id) REFERENCES tt_nodes(id) ON DELETE CASCADE
);

CREATE TABLE audit_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  actor_user_id INT NULL,
  action VARCHAR(190) NOT NULL,
  payload TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
