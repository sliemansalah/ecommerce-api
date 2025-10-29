import axios from "axios";
import router from "../router";

// إنشاء instance من axios
const axiosInstance = axios.create({
  baseURL: "/api",
  headers: {
    "Content-Type": "application/json",
    "Accept": "application/json",
    "X-Requested-With": "XMLHttpRequest", // ✅ لـ Laravel
  },
  withCredentials: true, // ✅ لإرسال Cookies
});

// ✅ Request Interceptor - إضافة الـ Token تلقائياً
axiosInstance.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
      console.log("✅ Authorization header set:", config.headers.Authorization); // Debug
    } else {
      console.log("❌ No token found in localStorage!"); // Debug
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// ✅ Response Interceptor - معالجة الأخطاء
axiosInstance.interceptors.response.use(
  (response) => {
    return response;
  },
  (error) => {
    // 401 Unauthorized - المستخدم غير مصرح
    if (error.response?.status === 401) {
      localStorage.removeItem("token");
      localStorage.removeItem("user");

      // تجنب infinite loop
      if (router.currentRoute.value.name !== "Login") {
        router.push({ name: "Login" });
      }
    }

    // 403 Forbidden - ليس لديه صلاحية
    if (error.response?.status === 403) {
      console.error("Access Denied:", error.response.data.message);
    }

    // 404 Not Found
    if (error.response?.status === 404) {
      console.error("Resource Not Found");
    }

    // 422 Validation Error
    if (error.response?.status === 422) {
      console.error("Validation Error:", error.response.data.errors);
    }

    // 500 Server Error
    if (error.response?.status === 500) {
      console.error("Server Error:", error.response.data.message);
    }

    return Promise.reject(error);
  },
);

export default axiosInstance;
