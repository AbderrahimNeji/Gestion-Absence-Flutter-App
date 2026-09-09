// Override at build time: flutter run --dart-define=API_BASE_URL=http://<ip>/gest_absence_api
const String baseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://192.168.1.21/gest_absence_api',
);
