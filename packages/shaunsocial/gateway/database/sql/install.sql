INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Manage Gateways', 'admin.gateway.manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 11, '');

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_gateway', '1.0.0', NOW(), NOW());

INSERT INTO `{prefix}gateways` (`name`, `key`, `class`, `show`, `order`, `created_at`, `updated_at`) VALUES
('Paypal', 'paypal' ,'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Paypal', 1, 1, NOW(), NOW()),
('Stripe', 'stripe','Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Stripe', 1, 2, NOW(), NOW()),
('Apple', 'apple', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Apple', 0, 3, NOW(), NOW()),
('Google', 'google', 'Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Google', 0, 4, NOW(), NOW()),
('Wallet', 'wallet','Packages\\ShaunSocial\\Gateway\\Repositories\\Helpers\\Wallet', 0, 5, NOW(), NOW());