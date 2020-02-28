--
-- Core Tag
--
CREATE TABLE `core_tag` (
  `Tag_ID` int(11) NOT NULL,
  `tag_key` varchar(50) NOT NULL,
  `tag_label` varchar(255) NOT NULL,
  `created_by` int(11) NOT NULL DEFAULT 1,
  `created_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `modified_by` int(11) NOT NULL DEFAULT 1,
  `modified_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `core_tag`
  ADD PRIMARY KEY (`Tag_ID`),
  ADD UNIQUE KEY `tag_key` (`tag_key`);

ALTER TABLE `core_tag`
  MODIFY `Tag_ID` int(11) NOT NULL AUTO_INCREMENT;

INSERT INTO `core_tag` (`Tag_ID`, `tag_key`, `tag_label`) VALUES
(NULL, 'category', 'Category'),
(NULL, 'state', 'State');

--
-- Core Entity Tag
--
CREATE TABLE `core_entity_tag` (
  `Entitytag_ID` int(11) NOT NULL,
  `entity_form_idfs` varchar(50) NOT NULL,
  `tag_idfs` int(11) NOT NULL,
  `tag_value` varchar(255) NOT NULL,
  `tag_color` varchar(10) NOT NULL,
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
-- Core Entity Tag - Entity
--
CREATE TABLE `core_entity_tag_entity` (
  `entity_idfs` int(11) NOT NULL,
  `entity_tag_idfs` int(11) NOT NULL,
  `entity_type` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `core_entity_tag_entity`
  ADD PRIMARY KEY (`entity_idfs`,`entity_type`,`entity_tag_idfs`);

--
-- Permissions
--
INSERT INTO `permission` (`permission_key`, `module`, `label`, `nav_label`, `nav_href`, `show_in_menu`) VALUES
('add', 'OnePlace\\Tag\\Controller\\TagController', 'Add', '', '', 0),
('edit', 'OnePlace\\Tag\\Controller\\TagController', 'Edit', '', '', 0),
('index', 'OnePlace\\Tag\\Controller\\TagController', 'Index', 'Tags', '/tag', 1),
('list', 'OnePlace\\Tag\\Controller\\ApiController', 'List', '', '', 1),
('view', 'OnePlace\\Tag\\Controller\\TagController', 'View', '', '', 0),
('add', 'OnePlace\\Tag\\Controller\\EntityController', 'Add', '', '', 0);

--
-- TAG - Core Form
--
INSERT INTO `core_form` (`form_key`, `label`, `entity_class`, `entity_tbl_class`) VALUES
('entitytag-single', 'Entity Tag', 'OnePlace\\Tag\\Model\\Entitytag', 'OnePlace\\Tag\\Model\\EntityTagTable'),
('tag-single', 'Tag', 'OnePlace\\Tag\\Model\\Tag', 'OnePlace\\Tag\\Model\\TagTable');

--
-- TAG - Core Form Button
--
INSERT INTO `core_form_button` (`Button_ID`, `label`, `icon`, `title`, `href`, `class`, `append`, `form`, `mode`, `filter_check`, `filter_value`) VALUES
(NULL, 'Save Tag', 'fas fa-save', 'Save Tag', '#', 'primary saveForm', '', 'tag-single', 'link', '', ''),
(NULL, 'Edit Tag', 'fas fa-edit', 'Edit Tag', '/tag/edit/##ID##', 'primary', '', 'tag-view', 'link', '', ''),
(NULL, 'Add Tag', 'fas fa-plus', 'Add Tag', '/tag/add', 'primary', '', 'tag-index', 'link', '', ''),
(NULL, 'Add Entity Tag', 'fas fa-plus', 'Add Entity Tag', '/tag/entity/add/##ID##', 'primary', '', 'tag-view', 'link', '', ''),
(NULL, 'Save Entity Tag', 'fas fa-save', 'Save Entity Tag', '#', 'primary saveForm', '', 'entitytag-single', 'link', '', '');

--
-- TAG - Core Form Tab
--
INSERT INTO `core_form_tab` (`Tab_ID`, `form`, `title`, `subtitle`, `icon`, `counter`, `sort_id`, `filter_check`, `filter_value`) VALUES
('tag-base', 'tag-single', 'Tag', 'Base', 'fas fa-tags', '', '0', '', ''),
('tag-entitytags', 'tag-single', 'Tags', 'Entity Tags', 'fas fa-tags', '', 0, '', ''),
('entitytag-base', 'entitytag-single', 'Entity Tag', 'Base', 'fas fa-tags', '', '0', '', '');

--
-- TAG - Core Form Field
--

INSERT INTO `core_form_field` (`Field_ID`, `type`, `label`, `fieldkey`, `tab`, `form`, `class`, `url_view`, `url_list`, `show_widget_left`, `allow_clear`, `readonly`, `tbl_cached_name`, `tbl_class`, `tbl_permission`) VALUES
(NULL, 'text', 'Name', 'tag_label', 'tag-base', 'tag-single', 'col-md-3', '/tag/view/##ID##', '', 0, 1, 0, '', '', ''),
(NULL, 'text', 'Key', 'tag_key', 'tag-base', 'tag-single', 'col-md-3', '/tag/view/##ID##', '', 0, 1, 1, '', '', ''),
(NULL, 'partial', 'Entity Tags', 'entitytags', 'tag-entitytags', 'tag-single', 'col-md-12', '', '', '0', '1', '0', '', '', ''),
(NULL, 'text', 'Name', 'tag_value', 'entitytag-base', 'entitytag-single', 'col-md-3', '/tag/entity/view/##ID##', '', '0', '1', '0', '', '', ''),
(NULL, 'text', 'Form Name', 'entity_form_idfs', 'entitytag-base', 'entitytag-single', 'col-md-3', '', '', '0', '1', '0', '', '', '');

--
-- TAG - Core Index Table
--
INSERT INTO `core_index_table` (`table_name`, `form`, `label`) VALUES ('tag-index', 'tag-single', 'Tag Index');

--
-- Tag Key for multiselect
--
ALTER TABLE `core_form_field` ADD `tag_key` VARCHAR(150) NOT NULL DEFAULT '' AFTER `url_list`;

--
-- icon
--
INSERT INTO `settings` (`settings_key`, `settings_value`) VALUES ('tag-icon', 'fas fa-tags');

--
-- Save
--
COMMIT;