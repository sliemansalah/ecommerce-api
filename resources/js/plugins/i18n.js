import { createI18n } from "vue-i18n";

// استيراد ملفات الترجمة
import ar from "../locales/ar.json";
import de from "../locales/de.json";
import en from "../locales/en.json";
import es from "../locales/es.json";
import fr from "../locales/fr.json";

// الحصول على اللغة المحفوظة أو استخدام العربية كافتراضي
const savedLocale = localStorage.getItem("user-locale") || "ar";

const i18n = createI18n({
  legacy: false, // استخدام Composition API
  locale: savedLocale,
  fallbackLocale: "en",
  messages: {
    ar,
    en,
    fr,
    es,
    de,
  },
});

export default i18n;
