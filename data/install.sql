--
-- Core Tag
--
CREATE TABLE `core_tag` (
  `Tag_ID` int(11) NOT NULL,
  `tag_key` varchar(50) NOT NULL,
  `tag_label` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `core_tag` (`Tag_ID`, `tag_key`, `tag_label`) VALUES
(1, 'category', 'Category'),
(2, 'state', 'State');

ALTER TABLE `core_tag`
  ADD PRIMARY KEY (`Tag_ID`),
  ADD UNIQUE KEY `tag_key` (`tag_key`);

ALTER TABLE `core_tag`
  MODIFY `Tag_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Core Entity Tag
--
CREATE TABLE `core_entity_tag` (
  `Entitytag_ID` int(11) NOT NULL,
  `entity_form_idfs` varchar(50) NOT NULL,
  `tag_idfs` int(11) NOT NULL,
  `tag_value` varchar(255) NOT NULL,
  `parent_tag_idfs` int(11) NOT NULL DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_date` datetime NOT NULL,
  `modified_by` int(11) NOT NULL,
  `modified_date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `core_entity_tag`
  ADD PRIMARY KEY (`Entitytag_ID`);

ALTER TABLE `core_entity_tag`
  MODIFY `Entitytag_ID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Permissions
--
  INSERT INTO `permission` (`permission_key`, `module`, `label`, `show_in_menu`) VALUES
('add', 'OnePlace\\Tag\\Controller\\TagController', 'Add', 0),
('edit', 'OnePlace\\Tag\\Controller\\TagController', 'Edit', 0),
('view', 'OnePlace\\Tag\\Controller\\TagController', 'View', 0),
('index', 'OnePlace\\Tag\\Controller\\TagController', 'Index', 1),
('list', 'OnePlace\\Tag\\Controller\\ApiController', 'List', 1);

--
-- Save
--
COMMIT;