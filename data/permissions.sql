INSERT INTO `permission` (`permission_key`, `module`, `label`, `show_in_menu`) VALUES
('add', 'OnePlace\\Tag\\Controller\\TagController', 'Add', 0),
('edit', 'OnePlace\\Tag\\Controller\\TagController', 'Edit', 0),
('view', 'OnePlace\\Tag\\Controller\\TagController', 'View', 0),
('index', 'OnePlace\\Tag\\Controller\\TagController', 'Index', 1),
('list', 'OnePlace\\Tag\\Controller\\ApiController', 'List', 1);
COMMIT;