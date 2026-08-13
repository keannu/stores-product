import { createRouter, createWebHistory } from 'vue-router';
import Home from '../views/Home.vue';
import Login from '../views/Login/Login.vue';
import NotFound from '../views/NotFound/NotFound.vue';

const routes = [
    { path: '/', component: Home },
    { path: '/login', component: Login },
    { path: '/:pathMatch(.*)*', component: NotFound },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
