import middleware from './middleware';
import { i18n } from '@/i18n'
import { computed } from 'vue';
import { loadPageView } from '../utility/index';

export default [
    {
        path: '/lists',
        name: 'lists',
        component: loadPageView('lists'),
        beforeEnter: middleware.user,
        meta: { title: computed(() => i18n.global.t('Lists')) },
        children: [
            {
                path: '',
                name: 'list_follower',
                component: () => import('@/pages/lists/ListFollower.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('List Follower')) },
            },
            {
                path: 'following',
                name: 'list_following',
                component: () => import('@/pages/lists/ListFollowing.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('Lists Following')) },
            },
            {
                path: 'tag',
                name: 'list_tag',
                component: () => import('@/pages/lists/ListTag.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('List Tag')) },
            },
            {
                path: 'block_member',
                name: 'list_block_member',
                component: () => import('@/pages/lists/ListBlockMember.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('List Block Member')) },
            },
            {
                path: 'stories',
                name: 'list_stories',
                component: () => import('@/pages/lists/ListStories.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('List Stories')) },
            },
            {
                path: 'pages',
                name: 'list_page',
                component: () => import('@/pages/lists/ListPage.vue'),
                beforeEnter: middleware.user,
                meta: { title: computed(() => i18n.global.t('List Pages')) },
            }
        ]
    }
]