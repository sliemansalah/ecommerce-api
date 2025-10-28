import { computed } from "vue";
import { useI18n } from "vue-i18n";
import { useRtl } from "vuetify";

export function useLanguage() {
  const { locale, t } = useI18n();
  const { isRtl } = useRtl();

  // اللغات المتاحة
  const availableLocales = [
    { code: "ar", name: "العربية", dir: "rtl", flag: "🇸🇦" },
    { code: "en", name: "English", dir: "ltr", flag: "🇬🇧" },
    { code: "fr", name: "Français", dir: "ltr", flag: "🇫🇷" },
    { code: "es", name: "Español", dir: "ltr", flag: "🇪🇸" },
    { code: "de", name: "Deutsch", dir: "ltr", flag: "🇩🇪" },
  ];

  // اللغة الحالية
  const currentLocale = computed(() => {
    return availableLocales.find(l => l.code === locale.value) || availableLocales[0];
  });

  // تغيير اللغة
  const changeLocale = (newLocale) => {
    locale.value = newLocale;

    // تحديث RTL
    const localeObj = availableLocales.find(l => l.code === newLocale);
    if (localeObj) {
      isRtl.value = localeObj.dir === "rtl";
      document.documentElement.setAttribute("dir", localeObj.dir);
      document.documentElement.setAttribute("lang", newLocale);
    }

    // حفظ في LocalStorage
    localStorage.setItem("user-locale", newLocale);
  };

  // تهيئة الاتجاه عند التحميل
  const initDirection = () => {
    const localeObj = availableLocales.find(l => l.code === locale.value);
    if (localeObj) {
      isRtl.value = localeObj.dir === "rtl";
      document.documentElement.setAttribute("dir", localeObj.dir);
      document.documentElement.setAttribute("lang", locale.value);
    }
  };

  return {
    locale,
    t,
    availableLocales,
    currentLocale,
    changeLocale,
    initDirection,
  };
}
