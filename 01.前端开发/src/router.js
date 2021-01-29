const publicRoutes = [
    {
        name: 'login',
        path: '/login',
        meta: {
            requireAuth: false,
        },
        component: (resolve) => require(['./views/login/login.vue'], resolve)
    },
    {
        name: 'manage',
        path: '/',
        redirect: 'Flights',
        meta: {
            requireAuth: true,
        },
        component: (resolve) => require(['./views/layout/BasicLayout.vue'], resolve),
        children: [
            {
                name: 'Flights',
                path: 'Flights',
                meta: {
                    title: '航班',
                    isNav: true,
                    requireAuth: true,
                },
                component: (resolve) => require(['./views/Flights/Flights.vue'], resolve)
            },
            {
                name: 'Hotel',
                path: 'Hotel',
                meta: {
                    title: '宾馆',
                    isNav: true,
                    requireAuth: true,
                },
                component: (resolve) => require(['./views/Hotel/Hotel.vue'], resolve),
            },
            {
                name: 'Cars',
                path: 'Cars',
                meta: {
                    title: '出租车',
                    isNav: true,
                    requireAuth: true,
                },
                component: (resolve) => require(['./views/Cars/Cars.vue'], resolve)
            },
            {
                name: 'advance',
                path: 'advance',
                meta: {
                    title: '高级管理',
                    isNav: true,
                    requireAuth: true,
                },
                component: (resolve) => require(['./views/advance/advance.vue'], resolve),
            },
        ]
    },
];



export default publicRoutes;
