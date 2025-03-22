INSERT INTO `{prefix}permission_groups` (`name`, `key`, `created_at`, `updated_at`, `package`, `order`) VALUES
('Story', 'story', NOW(), NOW(), 'shaun_story', (SELECT MAX(p.`order`) + 1 FROM `{prefix}permission_groups` as p));

INSERT INTO `{prefix}permissions` (`name`, `key`, `is_support_guest`, `group_id`, `created_at`, `updated_at`, `type`, `is_support_moderator`, `order`, `description`) VALUES
('Story backgrounds', 'admin.story.background_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 24, ''),
('Story songs', 'admin.story.song_manage', 0, 1, NOW(), NOW(), 'checkbox', 1, 25, ''),
('Allow create story', 'story.allow_create', 0, (SELECT `id` FROM `{prefix}permission_groups` WHERE `key` = 'story'), NOW(), NOW(), 'checkbox', 0, (SELECT MAX(p.`order`) + 1 FROM `{prefix}permissions` as p), '');

INSERT INTO `{prefix}role_permissions` (`role_id`, `permission_id`, `value`) VALUES
(2, (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'story.allow_create'), '1');

INSERT INTO `{prefix}model_maps` (`subject_type`, `model_class`) VALUES
('story_items', 'Packages\\ShaunSocial\\Story\\Models\\StoryItem');

INSERT INTO `{prefix}setting_group_subs` (`name`, `group_id`, `key`, `order`, `package`) VALUES 
('Story', 2, 'story', (SELECT MAX(p.`order`) + 1 FROM `{prefix}setting_group_subs` as p), 'shaun_story');

INSERT INTO `{prefix}settings` (`key`, `name`, `description`, `value`, `params`, `type`, `order`, `group_id`, `group_sub_id`, `hidden`) VALUES
('story.time_next_story', 'Time to move on to the next story (seconds)', '', '8', '', 'number', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'story'), 0),
('story.time_delete_story', 'Story item will automatically delete after (hours)', '', '24', '', 'number', 1, 2, (SELECT `id` FROM `{prefix}setting_group_subs` WHERE `key` = 'story'), 0);

INSERT INTO `{prefix}layout_blocks` (`component`, `title`, `enable`, `class`, `support_header_footer`, `package`, `created_at`, `updated_at`) VALUES
('Stories', 'Stories Block', 1, '', 0, 'shaun_story',NOW(), NOW());

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('layout_contents', 'title', 35, 'en', 'Stories Block'),
('layout_contents', 'content', 35, 'en', ''),
('layout_contents', 'title', 36, 'en', 'Stories Block'),
('layout_contents', 'content', 36, 'en', '');

INSERT INTO `{prefix}layout_contents` (`id`, `page_id`, `position`, `type`, `title`, `content`, `enable_title`, `component`, `view_type`, `role_access`, `params`, `order`, `class` ,`created_at`, `updated_at`, `package`) VALUES
(35, 3, 'center', 'component', 'Stories', NULL, 0, 'Stories', 'desktop', '["all"]' ,'{}', 1,'' ,NOW(), NOW(), 'shaun_story'),
(36, 3, 'center', 'component', 'Stories', NULL, 0, 'Stories', 'mobile', '["all"]' ,'{}', 1,'' ,NOW(), NOW(), 'shaun_story');

INSERT INTO `{prefix}story_backgrounds` (`photo_id`, `is_core`, `is_active`, `is_delete`, `order`, `created_at`, `updated_at`) VALUES
(0, 1, 1, 0, 29, NOW(), NOW()),
(0, 1, 1, 0, 28, NOW(), NOW()),
(0, 1, 1, 0, 27, NOW(), NOW()),
(0, 1, 1, 0, 26, NOW(), NOW()),
(0, 1, 1, 0, 25, NOW(), NOW()),
(0, 1, 1, 0, 24, NOW(), NOW()),
(0, 1, 1, 0, 23, NOW(), NOW()),
(0, 1, 1, 0, 22, NOW(), NOW()),
(0, 1, 1, 0, 21, NOW(), NOW()),
(0, 1, 1, 0, 20, NOW(), NOW()),
(0, 1, 1, 0, 19, NOW(), NOW()),
(0, 1, 1, 0, 18, NOW(), NOW()),
(0, 1, 1, 0, 17, NOW(), NOW()),
(0, 1, 1, 0, 16, NOW(), NOW()),
(0, 1, 1, 0, 15, NOW(), NOW()),
(0, 1, 1, 0, 14, NOW(), NOW()),
(0, 1, 1, 0, 13, NOW(), NOW()),
(0, 1, 1, 0, 12, NOW(), NOW()),
(0, 1, 1, 0, 11, NOW(), NOW()),
(0, 1, 1, 0, 10, NOW(), NOW()),
(0, 1, 1, 0, 9, NOW(), NOW()),
(0, 1, 1, 0, 8, NOW(), NOW()),
(0, 1, 1, 0, 7, NOW(), NOW()),
(0, 1, 1, 0, 6, NOW(), NOW()),
(0, 1, 1, 0, 5, NOW(), NOW()),
(0, 1, 1, 0, 4, NOW(), NOW()),
(0, 1, 1, 0, 3, NOW(), NOW()),
(0, 1, 1, 0, 2, NOW(), NOW()),
(0, 1, 1, 0, 1, NOW(), NOW());

INSERT INTO `{prefix}packages` (`name`, `version`, `created_at`, `updated_at`) VALUES
('shaun_story', '1.2.0', NOW(), NOW());

INSERT INTO `{prefix}translations` (`table_name`, `column_name`, `foreign_key`, `locale`, `value`) VALUES
('permissions', 'message_error', (SELECT `id` FROM `{prefix}permissions` WHERE `key` = 'story.allow_create') ,'en', "You don't have story creation permission.");