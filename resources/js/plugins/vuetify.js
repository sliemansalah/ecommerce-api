// resources/js/plugins/vuetify.js
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';
import { aliases, mdi } from 'vuetify/iconsets/mdi';
import '@mdi/font/css/materialdesignicons.css';
import 'vuetify/styles';

// إعدادات الألوان المخصصة
const lightTheme = {
    dark: false,
    colors: {
        primary: '#1976D2',
        secondary: '#424242',
        accent: '#82B1FF',
        error: '#FF5252',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FB8C00',
        background: '#F5F5F5',
        surface: '#FFFFFF',
    },
};

const darkTheme = {
    dark: true,
    colors: {
        primary: '#2196F3',
        secondary: '#424242',
        accent: '#FF4081',
        error: '#FF5252',
        info: '#2196F3',
        success: '#4CAF50',
        warning: '#FB8C00',
        background: '#121212',
        surface: '#1E1E1E',
    },
};

export default createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
        aliases,
        sets: {
            mdi,
        },
    },
    theme: {
        defaultTheme: 'lightTheme',
        themes: {
            lightTheme,
            darkTheme,
        },
    },
    defaults: {
        VBtn: {
            style: 'text-transform: none;',
        },
        VTextField: {
            variant: 'outlined',
            density: 'comfortable',
        },
        VSelect: {
            variant: 'outlined',
            density: 'comfortable',
        },
        VTextarea: {
            variant: 'outlined',
            density: 'comfortable',
        },
        VAutocomplete: {
            variant: 'outlined',
            density: 'comfortable',
        },
    },
    locale: {
        locale: 'ar',
        fallback: 'en',
        rtl: {
            ar: true,
        },
    },
});
