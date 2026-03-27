import 'dart:convert';
import 'dart:io';

import 'package:dio/dio.dart';
import 'package:file_picker/file_picker.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:intl/intl.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:shared_preferences/shared_preferences.dart';

const String kApiBaseUrl = String.fromEnvironment(
  'API_BASE_URL',
  defaultValue: 'http://10.0.2.2:8000/api/v1',
);

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  SystemChrome.setSystemUIOverlayStyle(
    const SystemUiOverlayStyle(
      statusBarColor: Colors.transparent,
      statusBarIconBrightness: Brightness.dark,
    ),
  );
  try {
    await Firebase.initializeApp();
  } catch (_) {
    // Firebase optional during local/dev runs without google-services plist/json.
  }
  runApp(const EngineeringMobileApp());
}

// ==========================================
// THEME & CONSTANTS (NEW MODERN STYLE)
// ==========================================
class AppColors {
  static const primary = Color(0xFF4F46E5); // Indigo 600
  static const primaryLight = Color(0xFF818CF8);
  static const background = Color(0xFFF8FAFC); // Slate 50
  static const surface = Colors.white;
  static const textPrimary = Color(0xFF0F172A); // Slate 900
  static const textSecondary = Color(0xFF64748B); // Slate 500
  static const border = Color(0xFFE2E8F0); // Slate 200

  // Status Colors
  static const success = Color(0xFF10B981);
  static const successBg = Color(0xFFD1FAE5);
  static const warning = Color(0xFFF59E0B);
  static const warningBg = Color(0xFFFEF3C7);
  static const error = Color(0xFFEF4444);
  static const errorBg = Color(0xFFFEE2E2);
  static const info = Color(0xFF3B82F6);
  static const infoBg = Color(0xFFDBEAFE);
}

class AppStyles {
  static final cardShadow = [
    BoxShadow(
      color: Colors.black.withOpacity(0.03),
      blurRadius: 10,
      offset: const Offset(0, 4),
    ),
  ];
  static final cardBorderRadius = BorderRadius.circular(20);
}

// ==========================================
// APP ROOT
// ==========================================
class EngineeringMobileApp extends StatefulWidget {
  const EngineeringMobileApp({super.key});

  @override
  State<EngineeringMobileApp> createState() => _EngineeringMobileAppState();
}

class _EngineeringMobileAppState extends State<EngineeringMobileApp> {
  final SessionController _session = SessionController();
  late final ApiRepository _api = ApiRepository(_session);
  bool _bootstrapped = false;

  @override
  void initState() {
    super.initState();
    _bootstrap();
  }

  Future<void> _bootstrap() async {
    await _session.bootstrap();
    if (!mounted) return;
    setState(() => _bootstrapped = true);
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Engineering Ops',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(
          seedColor: AppColors.primary,
          background: AppColors.background,
          surface: AppColors.surface,
          surfaceTint: Colors.transparent,
        ),
        useMaterial3: true,
        scaffoldBackgroundColor: AppColors.background,
        appBarTheme: const AppBarTheme(
          backgroundColor: AppColors.background,
          elevation: 0,
          scrolledUnderElevation: 0,
          centerTitle: false,
          iconTheme: IconThemeData(color: AppColors.textPrimary),
          titleTextStyle: TextStyle(
            color: AppColors.textPrimary,
            fontSize: 20,
            fontWeight: FontWeight.w700,
            letterSpacing: -0.5,
          ),
        ),
        cardTheme: CardThemeData(
          elevation: 0,
          color: AppColors.surface,
          shape: RoundedRectangleBorder(
            borderRadius: AppStyles.cardBorderRadius,
            side: const BorderSide(color: Colors.transparent),
          ),
          margin: EdgeInsets.zero,
        ),
        inputDecorationTheme: InputDecorationTheme(
          filled: true,
          fillColor: AppColors.background,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 16,
          ),
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: BorderSide.none,
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: const BorderSide(color: AppColors.border, width: 1),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(16),
            borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
          ),
          labelStyle: const TextStyle(color: AppColors.textSecondary),
          hintStyle: const TextStyle(color: AppColors.textSecondary),
        ),
        filledButtonTheme: FilledButtonThemeData(
          style: FilledButton.styleFrom(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            elevation: 0,
            textStyle: const TextStyle(
              fontWeight: FontWeight.w600,
              fontSize: 15,
            ),
          ),
        ),
        elevatedButtonTheme: ElevatedButtonThemeData(
          style: ElevatedButton.styleFrom(
            padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            elevation: 0,
            backgroundColor: AppColors.surface,
            foregroundColor: AppColors.primary,
            side: const BorderSide(color: AppColors.border),
            textStyle: const TextStyle(
              fontWeight: FontWeight.w600,
              fontSize: 15,
            ),
          ),
        ),
      ),
      home: !_bootstrapped
          ? const Scaffold(body: Center(child: CircularProgressIndicator()))
          : AnimatedBuilder(
              animation: _session,
              builder: (context, _) {
                if (_session.isAuthenticated) {
                  return HomeShell(api: _api, session: _session);
                }
                return LoginPage(api: _api, session: _session);
              },
            ),
    );
  }
}

// ==========================================
// DATA MODELS & CONTROLLERS (UNCHANGED)
// ==========================================
class SessionController extends ChangeNotifier {
  static const String _tokenKey = 'api_token';
  static const String _userJsonKey = 'auth_user_json';

  String? _token;
  UserProfile? _user;

  String? get token => _token;
  UserProfile? get user => _user;
  bool get isAuthenticated =>
      _token != null && _token!.isNotEmpty && _user != null;

  Future<void> bootstrap() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_tokenKey);

    final userJson = prefs.getString(_userJsonKey);
    if (userJson != null && userJson.isNotEmpty) {
      _user = UserProfile.fromJson(
        jsonDecode(userJson) as Map<String, dynamic>,
      );
    }
    notifyListeners();
  }

  Future<void> saveSession(String token, UserProfile user) async {
    _token = token;
    _user = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, token);
    await prefs.setString(_userJsonKey, jsonEncode(user.toJson()));
    notifyListeners();
  }

  Future<void> updateUser(UserProfile user) async {
    _user = user;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userJsonKey, jsonEncode(user.toJson()));
    notifyListeners();
  }

  Future<void> clear() async {
    _token = null;
    _user = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userJsonKey);
    notifyListeners();
  }
}

class UserProfile {
  const UserProfile({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.roleName,
    this.departmentId,
    this.departmentName,
  });

  final int id;
  final String name;
  final String email;
  final String role;
  final String? roleName;
  final int? departmentId;
  final String? departmentName;

  bool get isEngineer => role == 'engineer';
  bool get isInspectionOfficer => role == 'inspection_officer';

  factory UserProfile.fromJson(Map<String, dynamic> json) {
    return UserProfile(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: (json['name'] as String?) ?? '-',
      email: (json['email'] as String?) ?? '-',
      role: (json['role'] as String?) ?? '-',
      roleName: json['role_name'] as String?,
      departmentId: (json['department_id'] as num?)?.toInt(),
      departmentName: json['department_name'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'role_name': roleName,
      'department_id': departmentId,
      'department_name': departmentName,
    };
  }
}

class ApiException implements Exception {
  const ApiException(this.message, {this.errors = const {}});
  final String message;
  final Map<String, List<String>> errors;
  @override
  String toString() => message;
}

class PagedResponse {
  const PagedResponse({
    required this.data,
    required this.currentPage,
    required this.lastPage,
    required this.total,
    required this.perPage,
    required this.raw,
  });

  final List<Map<String, dynamic>> data;
  final int currentPage;
  final int lastPage;
  final int total;
  final int perPage;
  final Map<String, dynamic> raw;

  factory PagedResponse.fromJson(Map<String, dynamic> json) {
    final rawData = (json['data'] as List<dynamic>? ?? <dynamic>[])
        .whereType<Map<String, dynamic>>()
        .toList();

    final meta = json['meta'] as Map<String, dynamic>? ?? <String, dynamic>{};

    return PagedResponse(
      data: rawData,
      currentPage: (meta['current_page'] as num?)?.toInt() ?? 1,
      lastPage: (meta['last_page'] as num?)?.toInt() ?? 1,
      total: (meta['total'] as num?)?.toInt() ?? rawData.length,
      perPage: (meta['per_page'] as num?)?.toInt() ?? rawData.length,
      raw: json,
    );
  }
}

class ApiRepository {
  ApiRepository(this._session)
    : _dio = Dio(
        BaseOptions(
          baseUrl: kApiBaseUrl,
          connectTimeout: const Duration(seconds: 20),
          receiveTimeout: const Duration(seconds: 30),
          headers: <String, dynamic>{'Accept': 'application/json'},
        ),
      ) {
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = _session.token;
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          return handler.next(options);
        },
      ),
    );
  }

  final Dio _dio;
  final SessionController _session;

  Future<UserProfile> login({
    required String email,
    required String password,
    String? deviceName,
  }) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/auth/login',
        data: <String, dynamic>{
          'email': email,
          'password': password,
          'device_name': deviceName ?? 'engineering-mobile',
        },
      ),
    );
    final map = _asMap(response.data);
    final token = (map['token'] as String?) ?? '';
    final userMap = _asMap(map['user']);
    final user = UserProfile.fromJson(userMap);
    await _session.saveSession(token, user);
    return user;
  }

  Future<void> logout() async {
    try {
      await _safeRequest(() => _dio.post<dynamic>('/auth/logout'));
    } finally {
      await _session.clear();
    }
  }

  Future<UserProfile> me() async {
    final response = await _safeRequest(() => _dio.get<dynamic>('/auth/me'));
    final user = UserProfile.fromJson(_extractDataMap(response.data));
    await _session.updateUser(user);
    return user;
  }

  Future<UserProfile> updateMe(Map<String, dynamic> payload) async {
    final response = await _safeRequest(
      () => _dio.put<dynamic>('/auth/me', data: payload),
    );
    final user = UserProfile.fromJson(_extractDataMap(response.data));
    await _session.updateUser(user);
    return user;
  }

  Future<Map<String, dynamic>> getEngineerPerformance({
    String? dateFrom,
    String? dateTo,
  }) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/engineer/performance',
        queryParameters: <String, dynamic>{
          'date_from': dateFrom,
          'date_to': dateTo,
        },
      ),
    );
    return _asMap(response.data);
  }

  Future<PagedResponse> getEngineerTasks({String? search, int page = 1}) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/engineer/tasks',
        queryParameters: <String, dynamic>{
          'search': search,
          'page': page,
          'per_page': 15,
        },
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<PagedResponse> getEngineerHistory({int page = 1}) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/engineer/tasks/history',
        queryParameters: <String, dynamic>{'page': page, 'per_page': 15},
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<PagedResponse> getEngineerSchedules({
    int page = 1,
    int perPage = 15,
    String? workDateFrom,
    String? workDateTo,
    String? status,
  }) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/engineer/schedules',
        queryParameters: <String, dynamic>{
          'page': page,
          'per_page': perPage,
          'work_date_from': workDateFrom,
          'work_date_to': workDateTo,
          'status': status,
        },
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<Map<String, dynamic>> getEngineerTaskDetail(int ticketId) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>('/engineer/tasks/$ticketId'),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> transitionTask(
    int ticketId,
    String action, {
    String? notes,
  }) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/engineer/tasks/$ticketId/$action',
        data: <String, dynamic>{'notes': notes},
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> addTaskWorklog(
    int ticketId, {
    required String description,
    String logType = 'progress',
  }) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/engineer/tasks/$ticketId/worklogs',
        data: <String, dynamic>{
          'log_type': logType,
          'description': description,
        },
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<PagedResponse> getMyInspections({String? status, int page = 1}) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/inspection/my-inspections',
        queryParameters: <String, dynamic>{
          'status': status,
          'page': page,
          'per_page': 15,
        },
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<Map<String, dynamic>> getInspectionDetail(int inspectionId) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>('/inspection/my-inspections/$inspectionId'),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> createInspection({
    required int templateId,
    required String inspectionDate,
    int? assetId,
    int? locationId,
    String? summaryNotes,
  }) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/inspection/my-inspections',
        data: <String, dynamic>{
          'inspection_template_id': templateId,
          'asset_id': assetId,
          'asset_location_id': locationId,
          'inspection_date': inspectionDate,
          'summary_notes': summaryNotes,
        },
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> updateInspectionItems(
    int inspectionId,
    List<Map<String, dynamic>> items,
  ) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/inspection/my-inspections/$inspectionId/items',
        data: <String, dynamic>{'items': items},
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> submitInspection(
    int inspectionId, {
    required String finalResult,
    String? summaryNotes,
    List<PlatformFile> supportingFiles = const <PlatformFile>[],
  }) async {
    final formMap = <String, dynamic>{
      'final_result': finalResult,
      'summary_notes': summaryNotes,
    };
    for (var index = 0; index < supportingFiles.length; index++) {
      final file = supportingFiles[index];
      if (file.path != null) {
        formMap['supporting_files[$index]'] = await MultipartFile.fromFile(
          file.path!,
          filename: file.name,
        );
      }
    }
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/inspection/my-inspections/$inspectionId/submit',
        data: FormData.fromMap(formMap),
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<Map<String, dynamic>> uploadInspectionEvidence(
    int inspectionId, {
    required PlatformFile file,
    int? inspectionItemId,
    String? notes,
  }) async {
    if (file.path == null)
      throw const ApiException('File path tidak ditemukan.');
    final multipartFile = await MultipartFile.fromFile(
      file.path!,
      filename: file.name,
    );
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/inspection/my-inspections/$inspectionId/evidences',
        data: FormData.fromMap(<String, dynamic>{
          'file': multipartFile,
          'inspection_item_id': inspectionItemId,
          'notes': notes,
        }),
      ),
    );
    return _extractDataMap(response.data);
  }

  Future<PagedResponse> getInspectionResults({int page = 1}) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/inspection/results',
        queryParameters: <String, dynamic>{'page': page, 'per_page': 15},
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<PagedResponse> getInspectionTemplates() async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/inspection/templates',
        queryParameters: const <String, dynamic>{'per_page': 100},
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<PagedResponse> getInspectionAssets() async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/inspection/assets',
        queryParameters: const <String, dynamic>{'per_page': 100},
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<PagedResponse> getInspectionLocations() async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/inspection/asset-locations',
        queryParameters: const <String, dynamic>{'per_page': 100},
      ),
    );
    return PagedResponse.fromJson(_asMap(response.data));
  }

  Future<Map<String, dynamic>> getMobileNotifications({int limit = 25}) async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>(
        '/mobile/notifications',
        queryParameters: <String, dynamic>{'limit': limit},
      ),
    );

    return _asMap(response.data);
  }

  Future<Map<String, dynamic>> getFirebaseNotificationConfig() async {
    final response = await _safeRequest(
      () => _dio.get<dynamic>('/mobile/notifications/firebase-config'),
    );

    return _asMap(response.data);
  }

  Future<Map<String, dynamic>> registerDeviceToken({
    required String token,
    required String platform,
    String? deviceName,
    String? appVersion,
  }) async {
    final response = await _safeRequest(
      () => _dio.post<dynamic>(
        '/mobile/notifications/device-token',
        data: <String, dynamic>{
          'token': token,
          'platform': platform,
          'device_name': deviceName,
          'app_version': appVersion,
        },
      ),
    );

    return _asMap(response.data);
  }

  Future<void> autoRegisterPushToken() async {
    try {
      final messaging = FirebaseMessaging.instance;
      if (!kIsWeb && Platform.isIOS) {
        await messaging.requestPermission(
          alert: true,
          badge: true,
          sound: true,
        );
      }

      final token = await messaging.getToken();
      if (token == null || token.isEmpty) {
        return;
      }

      final packageInfo = await PackageInfo.fromPlatform();
      await registerDeviceToken(
        token: token,
        platform: _currentPlatform(),
        deviceName: packageInfo.appName,
        appVersion: '${packageInfo.version}+${packageInfo.buildNumber}',
      );

      messaging.onTokenRefresh.listen((newToken) async {
        try {
          await registerDeviceToken(
            token: newToken,
            platform: _currentPlatform(),
            deviceName: packageInfo.appName,
            appVersion: '${packageInfo.version}+${packageInfo.buildNumber}',
          );
        } catch (_) {}
      });
    } catch (_) {}
  }

  String _currentPlatform() {
    if (kIsWeb) {
      return 'web';
    }

    if (Platform.isIOS) {
      return 'ios';
    }

    return 'android';
  }

  Future<Response<dynamic>> _safeRequest(
    Future<Response<dynamic>> Function() executor,
  ) async {
    try {
      return await executor();
    } on DioException catch (error) {
      throw _mapDioException(error);
    }
  }

  ApiException _mapDioException(DioException error) {
    final responseData = error.response?.data;
    final map = _asMap(responseData);
    final message =
        (map['message'] as String?) ??
        error.message ??
        'Terjadi kendala saat menghubungi server.';
    final rawErrors = map['errors'];
    final parsedErrors = <String, List<String>>{};
    if (rawErrors is Map<String, dynamic>) {
      for (final entry in rawErrors.entries) {
        final value = entry.value;
        if (value is List) {
          parsedErrors[entry.key] = value
              .map((item) => item.toString())
              .toList();
        }
      }
    }
    return ApiException(message, errors: parsedErrors);
  }

  Map<String, dynamic> _extractDataMap(dynamic raw) {
    final map = _asMap(raw);
    final data = map['data'];
    if (data is Map<String, dynamic>) return data;
    return map;
  }

  Map<String, dynamic> _asMap(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    return <String, dynamic>{};
  }
}

// ==========================================
// SCREENS & WIDGETS (MODERNIZED)
// ==========================================

class LoginPage extends StatefulWidget {
  const LoginPage({super.key, required this.api, required this.session});
  final ApiRepository api;
  final SessionController session;

  @override
  State<LoginPage> createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _emailController.text = 'engineer@example.com';
    _passwordController.text = 'password';
  }

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    setState(() => _submitting = true);
    try {
      await widget.api.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );
      await widget.api.me();
    } on ApiException catch (error) {
      if (!mounted) return;
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showSnack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(message), behavior: SnackBarBehavior.floating),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [
              Color(0xFFEEF2FF),
              Color(0xFFE0E7FF),
            ], // Soft indigo gradient
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 400),
                child: Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: [
                      BoxShadow(
                        color: AppColors.primary.withOpacity(0.08),
                        blurRadius: 24,
                        offset: const Offset(0, 12),
                      ),
                    ],
                  ),
                  padding: const EdgeInsets.all(32),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: <Widget>[
                      const Center(child: HeaderLogoPlaceholder(size: 64)),
                      const SizedBox(height: 24),
                      Text(
                        'Welcome Back',
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.headlineSmall
                            ?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: AppColors.textPrimary,
                            ),
                      ),
                      const SizedBox(height: 8),
                      Text(
                        'Engineering Operations Mobile',
                        textAlign: TextAlign.center,
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          color: AppColors.textSecondary,
                        ),
                      ),
                      const SizedBox(height: 32),
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          labelText: 'Email Address',
                          prefixIcon: Icon(Icons.email_outlined),
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextField(
                        controller: _passwordController,
                        obscureText: true,
                        decoration: const InputDecoration(
                          labelText: 'Password',
                          prefixIcon: Icon(Icons.lock_outline),
                        ),
                      ),
                      const SizedBox(height: 32),
                      FilledButton(
                        onPressed: _submitting ? null : _submit,
                        child: _submitting
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                  color: Colors.white,
                                ),
                              )
                            : const Text('Sign In'),
                      ),
                      const SizedBox(height: 24),
                      Text(
                        'API: $kApiBaseUrl',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                          fontSize: 10,
                          color: AppColors.textSecondary.withOpacity(0.5),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class HomeShell extends StatefulWidget {
  const HomeShell({super.key, required this.api, required this.session});
  final ApiRepository api;
  final SessionController session;

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _selectedIndex = 0;
  bool _pushRegistered = false;

  @override
  void initState() {
    super.initState();
    _registerPushTokenIfNeeded();
  }

  Future<void> _registerPushTokenIfNeeded() async {
    if (_pushRegistered) {
      return;
    }

    _pushRegistered = true;
    await widget.api.autoRegisterPushToken();
  }

  List<_NavItem> _buildItems(UserProfile user) {
    if (user.isEngineer) {
      return <_NavItem>[
        _NavItem(
          title: 'Dashboard',
          icon: Icons.space_dashboard_rounded,
          page: EngineerDashboardPage(api: widget.api, session: widget.session),
        ),
        _NavItem(
          title: 'Tasks',
          icon: Icons.task_alt_rounded,
          page: EngineerTasksPage(api: widget.api),
        ),
        _NavItem(
          title: 'Inspections',
          icon: Icons.fact_check_rounded,
          page: MyInspectionsPage(api: widget.api),
        ),
        _NavItem(
          title: 'Schedule',
          icon: Icons.calendar_month_rounded,
          page: EngineerSchedulesPage(api: widget.api),
        ),
        _NavItem(
          title: 'Profile',
          icon: Icons.person_rounded,
          page: ProfilePage(api: widget.api, session: widget.session),
        ),
      ];
    }
    if (user.isInspectionOfficer) {
      return <_NavItem>[
        _NavItem(
          title: 'Inspections',
          icon: Icons.fact_check_rounded,
          page: MyInspectionsPage(api: widget.api),
        ),
        _NavItem(
          title: 'Results',
          icon: Icons.analytics_rounded,
          page: InspectionResultsPage(api: widget.api),
        ),
        _NavItem(
          title: 'Profile',
          icon: Icons.person_rounded,
          page: ProfilePage(api: widget.api, session: widget.session),
        ),
      ];
    }
    return <_NavItem>[
      _NavItem(
        title: 'Profile',
        icon: Icons.person_rounded,
        page: ProfilePage(api: widget.api, session: widget.session),
      ),
    ];
  }

  Future<void> _logout() async => await widget.api.logout();

  void _openProfileTab(List<_NavItem> items) {
    final profileIndex = items.indexWhere((item) => item.title == 'Profile');
    if (profileIndex >= 0) {
      setState(() {
        _selectedIndex = profileIndex;
      });
    }
  }

  Future<void> _openNotifications() async {
    await Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => MobileNotificationsPage(api: widget.api),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = widget.session.user;
    if (user == null)
      return const Scaffold(body: Center(child: CircularProgressIndicator()));

    final items = _buildItems(user);
    if (_selectedIndex >= items.length) _selectedIndex = 0;

    return Scaffold(
      appBar: AppBar(
        toolbarHeight: 70,
        titleSpacing: 20,
        title: Row(
          children: <Widget>[
            const HeaderLogoPlaceholder(size: 36),
            const SizedBox(width: 12),
            Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: <Widget>[
                Text(
                  'Taplox',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                    color: AppColors.textSecondary,
                  ),
                ),
                Text(
                  items[_selectedIndex].title,
                  style: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
              ],
            ),
          ],
        ),
        actions: <Widget>[
          IconButton(
            onPressed: _openNotifications,
            tooltip: 'Notifications',
            icon: const Icon(Icons.notifications_none_rounded),
          ),
          Padding(
            padding: const EdgeInsets.only(right: 12),
            child: PopupMenuButton<String>(
              onSelected: (value) async {
                if (value == 'profile') {
                  _openProfileTab(items);
                  return;
                }

                if (value == 'logout') {
                  await _logout();
                }
              },
              itemBuilder: (_) => const <PopupMenuEntry<String>>[
                PopupMenuItem<String>(
                  value: 'profile',
                  child: Row(
                    children: [
                      Icon(Icons.person_outline_rounded, size: 18),
                      SizedBox(width: 10),
                      Text('Profile'),
                    ],
                  ),
                ),
                PopupMenuItem<String>(
                  value: 'logout',
                  child: Row(
                    children: [
                      Icon(Icons.logout_rounded, size: 18),
                      SizedBox(width: 10),
                      Text('Logout'),
                    ],
                  ),
                ),
              ],
              child: CircleAvatar(
                radius: 16,
                backgroundColor: AppColors.primaryLight,
                child: Text(
                  user.name.isEmpty ? 'U' : user.name[0].toUpperCase(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
      body: Column(
        children: <Widget>[
          // Modern User Card at top
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 8),
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: AppStyles.cardShadow,
              ),
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 24,
                    backgroundColor: AppColors.primaryLight.withOpacity(0.2),
                    child: Text(
                      user.name.isEmpty ? 'U' : user.name[0].toUpperCase(),
                      style: const TextStyle(
                        color: AppColors.primary,
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                      ),
                    ),
                  ),
                  const SizedBox(width: 16),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user.name,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          '${user.roleName ?? user.role} • ${user.departmentName ?? '-'}',
                          style: const TextStyle(
                            color: AppColors.textSecondary,
                            fontSize: 13,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
          Expanded(
            child: IndexedStack(
              index: _selectedIndex,
              children: items.map((item) => item.page).toList(),
            ),
          ),
        ],
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: _selectedIndex,
        onDestinationSelected: (index) =>
            setState(() => _selectedIndex = index),
        backgroundColor: Colors.white,
        elevation: 10,
        shadowColor: Colors.black.withOpacity(0.1),
        indicatorColor: AppColors.primary.withOpacity(0.1),
        labelBehavior: NavigationDestinationLabelBehavior.alwaysShow,
        destinations: items
            .map(
              (item) => NavigationDestination(
                icon: Icon(item.icon, color: AppColors.textSecondary),
                selectedIcon: Icon(item.icon, color: AppColors.primary),
                label: item.title,
              ),
            )
            .toList(),
      ),
    );
  }
}

class _NavItem {
  const _NavItem({required this.title, required this.icon, required this.page});
  final String title;
  final IconData icon;
  final Widget page;
}

class EngineerDashboardPage extends StatefulWidget {
  const EngineerDashboardPage({
    super.key,
    required this.api,
    required this.session,
  });
  final ApiRepository api;
  final SessionController session;
  @override
  State<EngineerDashboardPage> createState() => _EngineerDashboardPageState();
}

class _EngineerDashboardPageState extends State<EngineerDashboardPage> {
  bool _loading = true;
  int _rangeDays = 30;
  Map<String, dynamic> _data = <String, dynamic>{};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final now = DateTime.now();
      final from = now.subtract(Duration(days: _rangeDays - 1));
      final response = await widget.api.getEngineerPerformance(
        dateFrom: DateFormat('yyyy-MM-dd').format(from),
        dateTo: DateFormat('yyyy-MM-dd').format(now),
      );
      setState(() => _data = response);
    } on ApiException catch (error) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final engineer =
        (_data['engineer'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final sla = (_data['sla'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final responseSla =
        (sla['response'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final resolutionSla =
        (sla['resolution'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final recentTickets =
        (_data['recent_tickets'] as List<dynamic>? ?? <dynamic>[])
            .whereType<Map<String, dynamic>>()
            .toList();

    final assigned = (engineer['assigned_tickets'] as num?)?.toInt() ?? 0;
    final completed = (engineer['completed_tickets'] as num?)?.toInt() ?? 0;
    final completionRate =
        (engineer['completion_rate'] as num?)?.toDouble() ?? 0;
    final effectiveness =
        (engineer['effectiveness_score'] as num?)?.toDouble() ?? 0;
    final responseCompliance =
        (responseSla['compliance_rate'] as num?)?.toDouble() ?? 0;
    final resolutionCompliance =
        (resolutionSla['compliance_rate'] as num?)?.toDouble() ?? 0;
    final avgResponseMinutes = (engineer['avg_response_minutes'] as num?)
        ?.toDouble();
    final avgResolutionMinutes = (engineer['avg_resolution_minutes'] as num?)
        ?.toDouble();

    return RefreshIndicator(
      onRefresh: _load,
      child: ListView(
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        children: <Widget>[
          // Sleek segmented control
          Container(
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              boxShadow: AppStyles.cardShadow,
            ),
            padding: const EdgeInsets.all(6),
            child: SegmentedButton<int>(
              style: SegmentedButton.styleFrom(
                backgroundColor: Colors.white,
                selectedBackgroundColor: AppColors.primary,
                selectedForegroundColor: Colors.white,
                side: BorderSide.none,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              showSelectedIcon: false,
              segments: const [
                ButtonSegment<int>(value: 7, label: Text('7 Hari')),
                ButtonSegment<int>(value: 14, label: Text('14 Hari')),
                ButtonSegment<int>(value: 30, label: Text('30 Hari')),
              ],
              selected: <int>{_rangeDays},
              onSelectionChanged: (value) {
                setState(() => _rangeDays = value.first);
                _load();
              },
            ),
          ),
          const SizedBox(height: 16),
          if (_loading)
            const Padding(
              padding: EdgeInsets.symmetric(vertical: 60),
              child: Center(child: CircularProgressIndicator()),
            )
          else ...<Widget>[
            GridView.count(
              crossAxisCount: 2,
              childAspectRatio: 1.4,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              children: <Widget>[
                StatCard(
                  title: 'Assigned',
                  value: '$assigned',
                  subtitle: 'Tickets',
                  icon: Icons.assignment_rounded,
                  color: AppColors.info,
                ),
                StatCard(
                  title: 'Completed',
                  value: '$completed',
                  subtitle: '${completionRate.toStringAsFixed(1)}%',
                  icon: Icons.check_circle_rounded,
                  color: AppColors.success,
                ),
                StatCard(
                  title: 'Effectiveness',
                  value: effectiveness.toStringAsFixed(1),
                  subtitle: 'Score',
                  icon: Icons.speed_rounded,
                  color: AppColors.primary,
                ),
                StatCard(
                  title: 'SLA Resolve',
                  value: '${resolutionCompliance.toStringAsFixed(1)}%',
                  subtitle: 'Compliance',
                  icon: Icons.timer_rounded,
                  color: AppColors.warning,
                ),
              ],
            ),
            const SizedBox(height: 16),
            ModernCard(
              title: 'SLA Time & Compliance',
              child: Column(
                children: <Widget>[
                  MetricProgressRow(
                    label: 'Completion Rate',
                    valueLabel: '${completionRate.toStringAsFixed(1)}%',
                    progress: (completionRate / 100).clamp(0, 1),
                    color: AppColors.success,
                  ),
                  const SizedBox(height: 16),
                  MetricProgressRow(
                    label: 'Response Comp.',
                    valueLabel: '${responseCompliance.toStringAsFixed(1)}%',
                    progress: (responseCompliance / 100).clamp(0, 1),
                    color: AppColors.primary,
                  ),
                  const SizedBox(height: 16),
                  MetricProgressRow(
                    label: 'Resolution Comp.',
                    valueLabel: '${resolutionCompliance.toStringAsFixed(1)}%',
                    progress: (resolutionCompliance / 100).clamp(0, 1),
                    color: AppColors.warning,
                  ),
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 16),
                    child: Divider(height: 1),
                  ),
                  InfoTile(
                    label: 'Avg Response',
                    value: avgResponseMinutes == null
                        ? '-'
                        : '${avgResponseMinutes.toStringAsFixed(1)} mnt',
                  ),
                  InfoTile(
                    label: 'Avg Resolution',
                    value: avgResolutionMinutes == null
                        ? '-'
                        : '${avgResolutionMinutes.toStringAsFixed(1)} mnt',
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            ModernCard(
              title: 'Recent Assigned Tickets',
              padding: EdgeInsets.zero,
              child: recentTickets.isEmpty
                  ? const Padding(
                      padding: EdgeInsets.all(20),
                      child: Text('Belum ada ticket.'),
                    )
                  : Column(
                      children: recentTickets
                          .map(
                            (ticket) => Container(
                              decoration: const BoxDecoration(
                                border: Border(
                                  bottom: BorderSide(
                                    color: AppColors.border,
                                    width: 0.5,
                                  ),
                                ),
                              ),
                              child: ListTile(
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 20,
                                  vertical: 8,
                                ),
                                leading: CircleAvatar(
                                  backgroundColor: AppColors.infoBg,
                                  child: const Icon(
                                    Icons.confirmation_num_rounded,
                                    color: AppColors.info,
                                    size: 20,
                                  ),
                                ),
                                title: Text(
                                  '${ticket['ticket_number'] ?? '-'}',
                                  style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                  ),
                                ),
                                subtitle: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      '${ticket['title'] ?? '-'}',
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                    const SizedBox(height: 4),
                                    Row(
                                      children: [
                                        StatusBadge(
                                          label: ticket['status_name'] ?? '-',
                                        ),
                                        const SizedBox(width: 8),
                                        Text(
                                          formatDate(ticket['created_at']),
                                          style: const TextStyle(
                                            fontSize: 11,
                                            color: AppColors.textSecondary,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          )
                          .toList(),
                    ),
            ),
            const SizedBox(height: 24),
          ],
        ],
      ),
    );
  }
}

class EngineerTasksPage extends StatefulWidget {
  const EngineerTasksPage({super.key, required this.api});
  final ApiRepository api;
  @override
  State<EngineerTasksPage> createState() => _EngineerTasksPageState();
}

class _EngineerTasksPageState extends State<EngineerTasksPage> {
  final TextEditingController _searchController = TextEditingController();
  bool _loading = true;
  List<Map<String, dynamic>> _tasks = <Map<String, dynamic>>[];
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _load({int page = 1}) async {
    setState(() => _loading = true);
    try {
      final response = await widget.api.getEngineerTasks(
        search: _searchController.text.trim().isEmpty
            ? null
            : _searchController.text.trim(),
        page: page,
      );
      setState(() {
        _tasks = response.data;
        _page = response.currentPage;
        _lastPage = response.lastPage;
      });
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showSnack(String message) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(message)));

  @override
  Widget build(BuildContext context) {
    return Column(
      children: <Widget>[
        Padding(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
          child: Row(
            children: <Widget>[
              Expanded(
                child: TextField(
                  controller: _searchController,
                  decoration: const InputDecoration(
                    hintText: 'Cari ticket...',
                    prefixIcon: Icon(Icons.search_rounded),
                  ),
                  onSubmitted: (_) => _load(page: 1),
                ),
              ),
              const SizedBox(width: 12),
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: AppStyles.cardShadow,
                ),
                child: IconButton(
                  onPressed: () => _load(page: 1),
                  icon: const Icon(
                    Icons.filter_list_rounded,
                    color: AppColors.primary,
                  ),
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: RefreshIndicator(
            onRefresh: () => _load(page: _page),
            child: _loading
                ? const Center(child: CircularProgressIndicator())
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 8,
                    ),
                    itemCount: _tasks.length + 1,
                    separatorBuilder: (_, __) => const SizedBox(height: 12),
                    itemBuilder: (context, index) {
                      if (index == _tasks.length) {
                        return PaginationBar(
                          page: _page,
                          lastPage: _lastPage,
                          onPrev: _page > 1
                              ? () => _load(page: _page - 1)
                              : null,
                          onNext: _page < _lastPage
                              ? () => _load(page: _page + 1)
                              : null,
                        );
                      }
                      final task = _tasks[index];
                      return Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: AppStyles.cardBorderRadius,
                          boxShadow: AppStyles.cardShadow,
                        ),
                        child: Material(
                          color: Colors.transparent,
                          child: InkWell(
                            borderRadius: AppStyles.cardBorderRadius,
                            onTap: () async {
                              await Navigator.of(context).push(
                                MaterialPageRoute<void>(
                                  builder: (_) => EngineerTaskDetailPage(
                                    api: widget.api,
                                    ticketId:
                                        (task['id'] as num?)?.toInt() ?? 0,
                                  ),
                                ),
                              );
                              _load(page: _page);
                            },
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        '${task['ticket_number'] ?? '-'}',
                                        style: const TextStyle(
                                          fontWeight: FontWeight.bold,
                                          color: AppColors.primary,
                                        ),
                                      ),
                                      StatusBadge(
                                        label:
                                            (task['ticket_status_name']
                                                as String?) ??
                                            '-',
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 8),
                                  Text(
                                    '${task['title'] ?? '-'}',
                                    style: const TextStyle(
                                      fontSize: 16,
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Icon(
                                        Icons.flag_rounded,
                                        size: 16,
                                        color: AppColors.textSecondary,
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        '${task['ticket_priority_name'] ?? '-'}',
                                        style: const TextStyle(
                                          color: AppColors.textSecondary,
                                          fontSize: 13,
                                        ),
                                      ),
                                      const Spacer(),
                                      Icon(
                                        Icons.timer_rounded,
                                        size: 16,
                                        color: AppColors.textSecondary,
                                      ),
                                      const SizedBox(width: 4),
                                      Text(
                                        formatDateTime(
                                          task['resolution_due_at'],
                                        ),
                                        style: const TextStyle(
                                          color: AppColors.textSecondary,
                                          fontSize: 13,
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ),
                      );
                    },
                  ),
          ),
        ),
      ],
    );
  }
}

class EngineerTaskDetailPage extends StatefulWidget {
  const EngineerTaskDetailPage({
    super.key,
    required this.api,
    required this.ticketId,
  });
  final ApiRepository api;
  final int ticketId;
  @override
  State<EngineerTaskDetailPage> createState() => _EngineerTaskDetailPageState();
}

class _EngineerTaskDetailPageState extends State<EngineerTaskDetailPage> {
  bool _loading = true;
  bool _submitting = false;
  Map<String, dynamic>? _task;

  final TextEditingController _transitionNotesController =
      TextEditingController();
  final TextEditingController _worklogController = TextEditingController();
  String _worklogType = 'progress';

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _transitionNotesController.dispose();
    _worklogController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final task = await widget.api.getEngineerTaskDetail(widget.ticketId);
      setState(() => _task = task);
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _runTransition(String action) async {
    setState(() => _submitting = true);
    try {
      await widget.api.transitionTask(
        widget.ticketId,
        action,
        notes: _transitionNotesController.text.trim().isEmpty
            ? null
            : _transitionNotesController.text.trim(),
      );
      _transitionNotesController.clear();
      await _load();
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _addWorklog() async {
    final description = _worklogController.text.trim();
    if (description.isEmpty)
      return _showSnack('Deskripsi worklog wajib diisi.');
    setState(() => _submitting = true);
    try {
      await widget.api.addTaskWorklog(
        widget.ticketId,
        description: description,
        logType: _worklogType,
      );
      _worklogController.clear();
      await _load();
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showSnack(String message) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(message)));

  bool _boolFlag(
    Map<String, dynamic> task,
    String key, {
    required bool fallback,
  }) {
    final raw = task[key];
    if (raw is bool) {
      return raw;
    }
    if (raw is num) {
      return raw != 0;
    }
    if (raw is String) {
      final normalized = raw.trim().toLowerCase();
      if (normalized == 'true' || normalized == '1') {
        return true;
      }
      if (normalized == 'false' || normalized == '0') {
        return false;
      }
    }

    return fallback;
  }

  @override
  Widget build(BuildContext context) {
    final task = _task;
    final statusCode = ((task?['ticket_status_code'] as String?) ?? '')
        .trim()
        .toUpperCase();
    final isTerminalFallback =
        task != null &&
        (task['completed_at'] != null ||
            task['closed_at'] != null ||
            statusCode == 'COMPLETED' ||
            statusCode == 'CLOSED');
    final canStartFallback =
        task != null && !isTerminalFallback && task['started_at'] == null;
    final canPauseFallback =
        task != null &&
        !isTerminalFallback &&
        task['started_at'] != null &&
        task['paused_at'] == null;
    final canResumeFallback =
        task != null &&
        !isTerminalFallback &&
        task['started_at'] != null &&
        task['paused_at'] != null;
    final canCompleteFallback =
        task != null && !isTerminalFallback && task['started_at'] != null;
    final canStart = task != null
        ? _boolFlag(task, 'can_start_work', fallback: canStartFallback)
        : false;
    final canPause = task != null
        ? _boolFlag(task, 'can_pause_work', fallback: canPauseFallback)
        : false;
    final canResume = task != null
        ? _boolFlag(task, 'can_resume_work', fallback: canResumeFallback)
        : false;
    final canComplete = task != null
        ? _boolFlag(task, 'can_complete_work', fallback: canCompleteFallback)
        : false;
    final hasTransitionAction =
        canStart || canPause || canResume || canComplete;

    return Scaffold(
      appBar: AppBar(title: const Text('Task Detail')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : task == null
          ? const Center(child: Text('Task tidak ditemukan'))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  // Header Card
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: AppStyles.cardBorderRadius,
                      boxShadow: AppStyles.cardShadow,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          '${task['ticket_number'] ?? '-'}',
                          style: TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          (task['title'] as String?) ?? '-',
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            height: 1.2,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: <Widget>[
                            StatusBadge(
                              label:
                                  (task['ticket_status_name'] as String?) ??
                                  '-',
                            ),
                            StatusBadge(
                              label:
                                  'Priority ${(task['ticket_priority_name'] as String?) ?? '-'}',
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Detail Informasi',
                    child: Column(
                      children: [
                        InfoTile(
                          label: 'Requester',
                          value: (task['requester_name'] as String?) ?? '-',
                        ),
                        InfoTile(
                          label: 'Service',
                          value: (task['service_name'] as String?) ?? '-',
                        ),
                        InfoTile(
                          label: 'Asset',
                          value: (task['asset_name'] as String?) ?? '-',
                        ),
                        InfoTile(
                          label: 'Lokasi',
                          value:
                              (task['asset_location_name'] as String?) ?? '-',
                        ),
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 8),
                          child: Divider(),
                        ),
                        InfoTile(
                          label: 'Resp. Due',
                          value: formatDateTime(task['response_due_at']),
                        ),
                        InfoTile(
                          label: 'Res. Due',
                          value: formatDateTime(task['resolution_due_at']),
                        ),
                        InfoTile(
                          label: 'Durasi Kerja',
                          value: formatDurationMinutes(
                            task['work_duration_minutes'],
                          ),
                        ),
                        const SizedBox(height: 16),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: AppColors.background,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            task['description'] as String? ??
                                'Tidak ada deskripsi.',
                          ),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Aksi Transisi',
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        if (hasTransitionAction) ...[
                          TextField(
                            controller: _transitionNotesController,
                            maxLines: 2,
                            decoration: const InputDecoration(
                              labelText: 'Catatan transisi (opsional)',
                            ),
                          ),
                          const SizedBox(height: 16),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            children: <Widget>[
                              if (canStart)
                                ElevatedButton(
                                  onPressed: _submitting
                                      ? null
                                      : () => _runTransition('start'),
                                  child: const Text('Start'),
                                ),
                              if (canPause)
                                ElevatedButton(
                                  onPressed: _submitting
                                      ? null
                                      : () => _runTransition('pause'),
                                  child: const Text('Pause'),
                                ),
                              if (canResume)
                                ElevatedButton(
                                  onPressed: _submitting
                                      ? null
                                      : () => _runTransition('resume'),
                                  child: const Text('Resume'),
                                ),
                              if (canComplete)
                                FilledButton(
                                  onPressed: _submitting
                                      ? null
                                      : () => _runTransition('complete'),
                                  child: const Text('Complete'),
                                ),
                            ],
                          ),
                        ] else
                          const Text(
                            'Tidak ada aksi transisi yang tersedia untuk task ini.',
                            style: TextStyle(color: AppColors.textSecondary),
                          ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Tambah Worklog',
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        DropdownButtonFormField<String>(
                          value: _worklogType,
                          decoration: const InputDecoration(
                            labelText: 'Tipe log',
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'note',
                              child: Text('Note'),
                            ),
                            DropdownMenuItem(
                              value: 'progress',
                              child: Text('Progress'),
                            ),
                            DropdownMenuItem(
                              value: 'resolution',
                              child: Text('Resolution'),
                            ),
                          ],
                          onChanged: (v) {
                            if (v != null) setState(() => _worklogType = v);
                          },
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _worklogController,
                          maxLines: 3,
                          decoration: const InputDecoration(
                            labelText: 'Deskripsi worklog',
                          ),
                        ),
                        const SizedBox(height: 16),
                        FilledButton.icon(
                          onPressed: _submitting ? null : _addWorklog,
                          icon: const Icon(Icons.add_comment_rounded),
                          label: const Text('Simpan Worklog'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Riwayat Worklog',
                    padding: EdgeInsets.zero,
                    child:
                        (task['worklogs'] as List<dynamic>? ?? <dynamic>[])
                            .isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(20),
                            child: Text('Belum ada worklog.'),
                          )
                        : Column(
                            children: (task['worklogs'] as List<dynamic>).map((
                              row,
                            ) {
                              final log = row as Map<String, dynamic>;
                              return ListTile(
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 20,
                                  vertical: 8,
                                ),
                                leading: CircleAvatar(
                                  backgroundColor: AppColors.primaryLight
                                      .withOpacity(0.2),
                                  child: const Icon(
                                    Icons.comment,
                                    size: 16,
                                    color: AppColors.primary,
                                  ),
                                ),
                                title: Text(
                                  (log['description'] as String?) ?? '-',
                                ),
                                subtitle: Text(
                                  '${log['user_name'] ?? '-'} • ${log['log_type'] ?? '-'} • ${formatDateTime(log['created_at'])}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                              );
                            }).toList(),
                          ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Activity Timeline',
                    padding: EdgeInsets.zero,
                    child:
                        (task['activities'] as List<dynamic>? ?? <dynamic>[])
                            .isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(20),
                            child: Text('Belum ada aktivitas.'),
                          )
                        : Column(
                            children: (task['activities'] as List<dynamic>).map((
                              row,
                            ) {
                              final activity = row as Map<String, dynamic>;
                              return ListTile(
                                contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 20,
                                  vertical: 4,
                                ),
                                leading: const Icon(
                                  Icons.history_rounded,
                                  color: AppColors.textSecondary,
                                ),
                                title: Text(
                                  (activity['activity_type'] as String?) ?? '-',
                                ),
                                subtitle: Text(
                                  '${activity['actor_user_name'] ?? '-'} • ${formatDateTime(activity['created_at'])}',
                                  style: const TextStyle(fontSize: 12),
                                ),
                              );
                            }).toList(),
                          ),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }
}

class EngineerHistoryPage extends StatefulWidget {
  const EngineerHistoryPage({super.key, required this.api});
  final ApiRepository api;
  @override
  State<EngineerHistoryPage> createState() => _EngineerHistoryPageState();
}

class _EngineerHistoryPageState extends State<EngineerHistoryPage> {
  bool _loading = true;
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({int page = 1}) async {
    setState(() => _loading = true);
    try {
      final response = await widget.api.getEngineerHistory(page: page);
      setState(() {
        _items = response.data;
        _page = response.currentPage;
        _lastPage = response.lastPage;
      });
    } on ApiException catch (error) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () => _load(page: _page),
      child: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              itemCount: _items.length + 1,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                if (index == _items.length) {
                  return PaginationBar(
                    page: _page,
                    lastPage: _lastPage,
                    onPrev: _page > 1 ? () => _load(page: _page - 1) : null,
                    onNext: _page < _lastPage
                        ? () => _load(page: _page + 1)
                        : null,
                  );
                }
                final item = _items[index];
                return Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppStyles.cardBorderRadius,
                    boxShadow: AppStyles.cardShadow,
                  ),
                  child: ListTile(
                    contentPadding: const EdgeInsets.all(16),
                    title: Text(
                      '${item['ticket_number'] ?? '-'} • ${item['title'] ?? '-'}',
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    subtitle: Padding(
                      padding: const EdgeInsets.only(top: 8),
                      child: Text(
                        'Completed at: ${formatDateTime(item['completed_at'])}',
                      ),
                    ),
                    trailing: StatusBadge(
                      label: (item['ticket_status_name'] as String?) ?? '-',
                    ),
                  ),
                );
              },
            ),
    );
  }
}

class EngineerSchedulesPage extends StatefulWidget {
  const EngineerSchedulesPage({super.key, required this.api});
  final ApiRepository api;
  @override
  State<EngineerSchedulesPage> createState() => _EngineerSchedulesPageState();
}

class _EngineerSchedulesPageState extends State<EngineerSchedulesPage> {
  bool _loading = true;
  DateTime _focusedMonth = DateTime(DateTime.now().year, DateTime.now().month);
  Map<String, List<Map<String, dynamic>>> _entriesByDate =
      <String, List<Map<String, dynamic>>>{};

  @override
  void initState() {
    super.initState();
    _loadMonth();
  }

  Future<void> _loadMonth() async {
    setState(() => _loading = true);

    final monthStart = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
    final monthEnd = DateTime(_focusedMonth.year, _focusedMonth.month + 1, 0);

    try {
      final response = await widget.api.getEngineerSchedules(
        page: 1,
        perPage: 200,
        workDateFrom: DateFormat('yyyy-MM-dd').format(monthStart),
        workDateTo: DateFormat('yyyy-MM-dd').format(monthEnd),
      );

      final map = <String, List<Map<String, dynamic>>>{};
      for (final entry in response.data) {
        final workDate = (entry['work_date'] as String?) ?? '';
        if (workDate.isEmpty) {
          continue;
        }
        map.putIfAbsent(workDate, () => <Map<String, dynamic>>[]).add(entry);
      }

      setState(() {
        _entriesByDate = map;
      });
    } on ApiException catch (error) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _nextMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month + 1);
    });
    _loadMonth();
  }

  void _previousMonth() {
    setState(() {
      _focusedMonth = DateTime(_focusedMonth.year, _focusedMonth.month - 1);
    });
    _loadMonth();
  }

  String _dateKey(DateTime date) => DateFormat('yyyy-MM-dd').format(date);

  Color _statusColor(String? status) {
    final normalized = (status ?? '').toLowerCase();
    if (normalized.contains('assigned')) {
      return AppColors.info;
    }
    if (normalized.contains('off') || normalized.contains('leave')) {
      return AppColors.warning;
    }
    if (normalized.contains('done') || normalized.contains('completed')) {
      return AppColors.success;
    }

    return AppColors.primary;
  }

  Future<void> _showDayDetails(
    DateTime date,
    List<Map<String, dynamic>> entries,
  ) async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (_) {
        return Padding(
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Text(
                'Schedule ${DateFormat('dd MMM yyyy').format(date)}',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
              const SizedBox(height: 12),
              if (entries.isEmpty)
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: AppColors.background,
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: AppColors.border),
                  ),
                  child: const Text(
                    'Tidak ada jadwal pada tanggal ini.',
                    style: TextStyle(color: AppColors.textSecondary),
                  ),
                )
              else
                ...entries.map(
                  (entry) => Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: AppColors.border),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          entry['shift_name'] as String? ?? '-',
                          style: const TextStyle(fontWeight: FontWeight.w600),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${entry['shift_start_time'] ?? '-'} - ${entry['shift_end_time'] ?? '-'}',
                          style: const TextStyle(
                            color: AppColors.textSecondary,
                          ),
                        ),
                        const SizedBox(height: 6),
                        StatusBadge(
                          label: (entry['status'] as String?) ?? '-',
                          color: _statusColor(entry['status'] as String?),
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final firstDay = DateTime(_focusedMonth.year, _focusedMonth.month, 1);
    final daysInMonth = DateUtils.getDaysInMonth(
      _focusedMonth.year,
      _focusedMonth.month,
    );
    final leadingEmptyCells = firstDay.weekday - 1;
    final totalCells = ((leadingEmptyCells + daysInMonth + 6) ~/ 7) * 7;
    final weekdayLabels = const [
      'Sen',
      'Sel',
      'Rab',
      'Kam',
      'Jum',
      'Sab',
      'Min',
    ];

    return RefreshIndicator(
      onRefresh: _loadMonth,
      child: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              children: <Widget>[
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppStyles.cardBorderRadius,
                    boxShadow: AppStyles.cardShadow,
                  ),
                  child: Row(
                    children: <Widget>[
                      IconButton(
                        onPressed: _previousMonth,
                        icon: const Icon(Icons.chevron_left_rounded),
                      ),
                      Expanded(
                        child: Text(
                          DateFormat('MMMM yyyy').format(_focusedMonth),
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 18,
                          ),
                        ),
                      ),
                      IconButton(
                        onPressed: _nextMonth,
                        icon: const Icon(Icons.chevron_right_rounded),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppStyles.cardBorderRadius,
                    boxShadow: AppStyles.cardShadow,
                  ),
                  child: Column(
                    children: <Widget>[
                      Row(
                        children: weekdayLabels
                            .map(
                              (label) => Expanded(
                                child: Center(
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(
                                      vertical: 6,
                                    ),
                                    child: Text(
                                      label,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: AppColors.textSecondary,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ),
                              ),
                            )
                            .toList(),
                      ),
                      const SizedBox(height: 6),
                      GridView.builder(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        itemCount: totalCells,
                        gridDelegate:
                            const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 7,
                              mainAxisSpacing: 6,
                              crossAxisSpacing: 6,
                              childAspectRatio: 0.8,
                            ),
                        itemBuilder: (context, index) {
                          final dayNumber = index - leadingEmptyCells + 1;
                          if (index < leadingEmptyCells ||
                              dayNumber > daysInMonth) {
                            return const SizedBox.shrink();
                          }

                          final date = DateTime(
                            _focusedMonth.year,
                            _focusedMonth.month,
                            dayNumber,
                          );
                          final key = _dateKey(date);
                          final entries =
                              _entriesByDate[key] ?? <Map<String, dynamic>>[];
                          final isToday = DateUtils.isSameDay(
                            date,
                            DateTime.now(),
                          );

                          return InkWell(
                            borderRadius: BorderRadius.circular(10),
                            onTap: () => _showDayDetails(date, entries),
                            child: Container(
                              padding: const EdgeInsets.all(6),
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(10),
                                color: isToday
                                    ? AppColors.primaryLight.withOpacity(0.12)
                                    : AppColors.background,
                                border: Border.all(
                                  color: isToday
                                      ? AppColors.primaryLight
                                      : AppColors.border,
                                ),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: <Widget>[
                                  Text(
                                    '$dayNumber',
                                    style: TextStyle(
                                      fontWeight: FontWeight.w700,
                                      color: isToday
                                          ? AppColors.primary
                                          : AppColors.textPrimary,
                                    ),
                                  ),
                                  const Spacer(),
                                  if (entries.isNotEmpty)
                                    Container(
                                      width: 18,
                                      height: 6,
                                      decoration: BoxDecoration(
                                        color: _statusColor(
                                          entries.first['status'] as String?,
                                        ),
                                        borderRadius: BorderRadius.circular(
                                          999,
                                        ),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                const Text(
                  'Tap tanggal untuk melihat detail shift pada hari tersebut.',
                  style: TextStyle(color: AppColors.textSecondary),
                ),
              ],
            ),
    );
  }
}

class MyInspectionsPage extends StatefulWidget {
  const MyInspectionsPage({super.key, required this.api});
  final ApiRepository api;
  @override
  State<MyInspectionsPage> createState() => _MyInspectionsPageState();
}

class _MyInspectionsPageState extends State<MyInspectionsPage> {
  bool _loading = true;
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({int page = 1}) async {
    setState(() => _loading = true);
    try {
      final response = await widget.api.getMyInspections(page: page);
      setState(() {
        _items = response.data;
        _page = response.currentPage;
        _lastPage = response.lastPage;
      });
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _showSnack(String message) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(message)));

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () => _load(page: _page),
      child: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView.separated(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              itemCount: _items.length + 1,
              separatorBuilder: (_, __) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                if (index == _items.length) {
                  return PaginationBar(
                    page: _page,
                    lastPage: _lastPage,
                    onPrev: _page > 1 ? () => _load(page: _page - 1) : null,
                    onNext: _page < _lastPage
                        ? () => _load(page: _page + 1)
                        : null,
                  );
                }
                final row = _items[index];
                return Container(
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppStyles.cardBorderRadius,
                    boxShadow: AppStyles.cardShadow,
                  ),
                  child: InkWell(
                    borderRadius: AppStyles.cardBorderRadius,
                    onTap: () async {
                      await Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => InspectionDetailPage(
                            api: widget.api,
                            inspectionId: (row['id'] as num?)?.toInt() ?? 0,
                          ),
                        ),
                      );
                      _load(page: _page);
                    },
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Text(
                                '${row['inspection_number'] ?? '-'}',
                                style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: AppColors.primary,
                                ),
                              ),
                              StatusBadge(
                                label: (row['status'] as String?) ?? '-',
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            '${row['asset_name'] ?? '-'}',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            'Tanggal: ${formatDate(row['inspection_date'])} • Final: ${(row['final_result'] as String?) ?? '-'}',
                            style: const TextStyle(
                              color: AppColors.textSecondary,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            'Schedule: ${(row['schedule_type'] as String?) ?? 'none'}'
                            '${row['schedule_next_date'] != null ? ' • Next: ${formatDate(row['schedule_next_date'])}' : ''}',
                            style: const TextStyle(
                              color: AppColors.textSecondary,
                              fontSize: 12,
                            ),
                          ),
                          if (row['linked_ticket_number'] != null)
                            Padding(
                              padding: const EdgeInsets.only(top: 8),
                              child: StatusBadge(
                                label: 'Ticket: ${row['linked_ticket_number']}',
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                );
              },
            ),
    );
  }
}

class InspectionDetailPage extends StatefulWidget {
  const InspectionDetailPage({
    super.key,
    required this.api,
    required this.inspectionId,
  });
  final ApiRepository api;
  final int inspectionId;
  @override
  State<InspectionDetailPage> createState() => _InspectionDetailPageState();
}

class _InspectionDetailPageState extends State<InspectionDetailPage> {
  bool _loading = true;
  bool _submitting = false;
  Map<String, dynamic>? _inspection;
  final Map<int, Map<String, dynamic>> _itemEdits =
      <int, Map<String, dynamic>>{};
  final TextEditingController _summaryController = TextEditingController();
  String _finalResult = 'normal';
  List<PlatformFile> _supportingFiles = <PlatformFile>[];

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _summaryController.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() => _loading = true);
    try {
      final detail = await widget.api.getInspectionDetail(widget.inspectionId);
      setState(() {
        _inspection = detail;
        _summaryController.text = (detail['summary_notes'] as String?) ?? '';
        _finalResult = (detail['final_result'] as String?) ?? 'normal';
      });
    } on ApiException catch (error) {
      _showSnack(error.message);
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _editItem(Map<String, dynamic> item) async {
    final itemId = (item['id'] as num?)?.toInt() ?? 0;
    final editState =
        _itemEdits[itemId] ??
        <String, dynamic>{
          'id': itemId,
          'result_status': item['result_status'],
          'result_value': item['result_value'],
          'notes': item['notes'],
        };
    String? resultStatus = editState['result_status'] as String?;
    final resultValueController = TextEditingController(
      text: editState['result_value']?.toString() ?? '',
    );
    final notesController = TextEditingController(
      text: editState['notes']?.toString() ?? '',
    );

    await showDialog<void>(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          title: Text(
            item['item_label'] as String? ?? '-',
            style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: <Widget>[
                DropdownButtonFormField<String?>(
                  value: resultStatus,
                  decoration: const InputDecoration(labelText: 'Result Status'),
                  items: const [
                    DropdownMenuItem(value: null, child: Text('- Not set -')),
                    DropdownMenuItem(value: 'pass', child: Text('Pass')),
                    DropdownMenuItem(value: 'fail', child: Text('Fail')),
                    DropdownMenuItem(value: 'na', child: Text('N/A')),
                  ],
                  onChanged: (v) => setDialogState(() => resultStatus = v),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: resultValueController,
                  decoration: const InputDecoration(labelText: 'Result Value'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: notesController,
                  maxLines: 3,
                  decoration: const InputDecoration(labelText: 'Notes'),
                ),
              ],
            ),
          ),
          actions: <Widget>[
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () {
                _itemEdits[itemId] = <String, dynamic>{
                  'id': itemId,
                  'result_status': resultStatus,
                  'result_value': resultValueController.text.trim().isEmpty
                      ? null
                      : resultValueController.text.trim(),
                  'notes': notesController.text.trim().isEmpty
                      ? null
                      : notesController.text.trim(),
                };
                Navigator.pop(context);
                setState(() {});
              },
              child: const Text('Save'),
            ),
          ],
        ),
      ),
    );
    resultValueController.dispose();
    notesController.dispose();
  }

  Future<void> _saveItems() async {
    if (_itemEdits.isEmpty) return _showSnack('Belum ada perubahan item.');
    setState(() => _submitting = true);
    try {
      await widget.api.updateInspectionItems(
        widget.inspectionId,
        _itemEdits.values.toList(),
      );
      _itemEdits.clear();
      await _load();
      _showSnack('Item inspeksi berhasil disimpan.');
    } on ApiException catch (e) {
      _showSnack(e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _uploadEvidence() async {
    final picked = await FilePicker.platform.pickFiles(
      withData: false,
      allowMultiple: false,
    );
    if (picked == null || picked.files.isEmpty) return;
    if (!mounted) return;

    final selectedFile = picked.files.first;
    int? selectedItemId;
    final notesController = TextEditingController();
    final items = (_inspection?['items'] as List<dynamic>? ?? <dynamic>[])
        .whereType<Map<String, dynamic>>()
        .toList();

    final shouldUpload = await showDialog<bool>(
      context: context,
      builder: (context) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          title: const Text('Upload Evidence'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: <Widget>[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.background,
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.insert_drive_file_rounded,
                      color: AppColors.primary,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        selectedFile.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<int?>(
                value: selectedItemId,
                decoration: const InputDecoration(
                  labelText: 'Inspection Item (opsional)',
                ),
                items: [
                  const DropdownMenuItem<int?>(
                    value: null,
                    child: Text('- None -'),
                  ),
                  ...items.map(
                    (item) => DropdownMenuItem<int?>(
                      value: (item['id'] as num?)?.toInt(),
                      child: Text(item['item_label'] as String? ?? '-'),
                    ),
                  ),
                ],
                onChanged: (v) => setDialogState(() => selectedItemId = v),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: notesController,
                maxLines: 2,
                decoration: const InputDecoration(labelText: 'Notes'),
              ),
            ],
          ),
          actions: <Widget>[
            TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Upload'),
            ),
          ],
        ),
      ),
    );

    final notes = notesController.text.trim().isEmpty
        ? null
        : notesController.text.trim();
    notesController.dispose();

    if (shouldUpload != true) return;
    setState(() => _submitting = true);
    try {
      await widget.api.uploadInspectionEvidence(
        widget.inspectionId,
        file: selectedFile,
        inspectionItemId: selectedItemId,
        notes: notes,
      );
      await _load();
      _showSnack('Evidence berhasil diupload.');
    } on ApiException catch (e) {
      _showSnack(e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _pickSupportingFiles() async {
    final picked = await FilePicker.platform.pickFiles(
      withData: false,
      allowMultiple: true,
    );
    if (picked != null) setState(() => _supportingFiles = picked.files);
  }

  Future<void> _submitInspection() async {
    if (_finalResult == 'abnormal' && _supportingFiles.isEmpty)
      return _showSnack('Final abnormal wajib upload file pendukung.');
    setState(() => _submitting = true);
    try {
      await widget.api.submitInspection(
        widget.inspectionId,
        finalResult: _finalResult,
        summaryNotes: _summaryController.text.trim().isEmpty
            ? null
            : _summaryController.text.trim(),
        supportingFiles: _supportingFiles,
      );
      _supportingFiles = <PlatformFile>[];
      await _load();
      _showSnack('Inspeksi berhasil disubmit.');
    } on ApiException catch (e) {
      _showSnack(e.message);
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  void _showSnack(String message) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(message)));

  @override
  Widget build(BuildContext context) {
    final detail = _inspection;
    return Scaffold(
      appBar: AppBar(title: const Text('Inspection Detail')),
      body: _loading
          ? const Center(child: CircularProgressIndicator())
          : detail == null
          ? const Center(child: Text('Data inspeksi tidak ditemukan.'))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: <Widget>[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(20),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: AppStyles.cardBorderRadius,
                      boxShadow: AppStyles.cardShadow,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          detail['inspection_number'] as String? ?? '-',
                          style: const TextStyle(
                            color: AppColors.primary,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Text(
                          '${detail['inspection_template_name'] ?? '-'}',
                          style: const TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            height: 1.2,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${detail['asset_name'] ?? '-'}',
                          style: const TextStyle(
                            color: AppColors.textSecondary,
                            fontSize: 16,
                          ),
                        ),
                        const SizedBox(height: 16),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: [
                            StatusBadge(
                              label: (detail['status'] as String?) ?? '-',
                            ),
                            StatusBadge(
                              label:
                                  'Final ${(detail['final_result'] as String?) ?? '-'}',
                            ),
                            if (detail['linked_ticket_number'] != null)
                              StatusBadge(
                                label:
                                    'Ticket ${detail['linked_ticket_number']}',
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Info Inspeksi',
                    child: Column(
                      children: [
                        InfoTile(
                          label: 'Officer',
                          value:
                              (detail['inspection_officer_name'] as String?) ??
                              '-',
                        ),
                        InfoTile(
                          label: 'Date',
                          value: formatDate(detail['inspection_date']),
                        ),
                        InfoTile(
                          label: 'Location',
                          value:
                              (detail['asset_location_name'] as String?) ?? '-',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Inspection Items',
                    action: IconButton(
                      onPressed: _submitting ? null : _saveItems,
                      icon: const Icon(
                        Icons.save_rounded,
                        color: AppColors.primary,
                      ),
                      tooltip: 'Save Item Changes',
                    ),
                    padding: EdgeInsets.zero,
                    child:
                        (_inspection?['items'] as List<dynamic>? ?? <dynamic>[])
                            .isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(20),
                            child: Text('Belum ada item.'),
                          )
                        : Column(
                            children: (_inspection?['items'] as List<dynamic>).map((
                              raw,
                            ) {
                              final item = raw as Map<String, dynamic>;
                              final itemId = (item['id'] as num?)?.toInt() ?? 0;
                              final edited = _itemEdits[itemId];
                              final statusLabel =
                                  (edited?['result_status'] as String?) ??
                                  (item['result_status'] as String?) ??
                                  '-';
                              final valueLabel =
                                  (edited?['result_value'] as String?) ??
                                  (item['result_value'] as String?) ??
                                  '-';

                              return Container(
                                decoration: const BoxDecoration(
                                  border: Border(
                                    bottom: BorderSide(
                                      color: AppColors.border,
                                      width: 0.5,
                                    ),
                                  ),
                                ),
                                child: ListTile(
                                  contentPadding: const EdgeInsets.symmetric(
                                    horizontal: 20,
                                    vertical: 8,
                                  ),
                                  title: Text(
                                    '${item['sequence'] ?? '-'} • ${item['item_label'] ?? '-'}',
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w600,
                                    ),
                                  ),
                                  subtitle: Padding(
                                    padding: const EdgeInsets.only(top: 4),
                                    child: Text(
                                      'Status: $statusLabel • Value: $valueLabel',
                                    ),
                                  ),
                                  trailing: IconButton(
                                    onPressed: _submitting
                                        ? null
                                        : () => _editItem(item),
                                    icon: const Icon(
                                      Icons.edit_rounded,
                                      color: AppColors.textSecondary,
                                    ),
                                  ),
                                ),
                              );
                            }).toList(),
                          ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Evidence',
                    action: IconButton(
                      onPressed: _submitting ? null : _uploadEvidence,
                      icon: const Icon(
                        Icons.upload_file_rounded,
                        color: AppColors.primary,
                      ),
                    ),
                    padding: EdgeInsets.zero,
                    child:
                        (_inspection?['evidences'] as List<dynamic>? ??
                                <dynamic>[])
                            .isEmpty
                        ? const Padding(
                            padding: EdgeInsets.all(20),
                            child: Text('Belum ada evidence.'),
                          )
                        : Column(
                            children: (_inspection?['evidences'] as List<dynamic>)
                                .map((raw) {
                                  final evidence = raw as Map<String, dynamic>;
                                  return ListTile(
                                    contentPadding: const EdgeInsets.symmetric(
                                      horizontal: 20,
                                      vertical: 4,
                                    ),
                                    leading: const Icon(
                                      Icons.image_rounded,
                                      color: AppColors.textSecondary,
                                    ),
                                    title: Text(
                                      evidence['original_name'] as String? ??
                                          '-',
                                    ),
                                    subtitle: Text(
                                      '${evidence['uploaded_by_name'] ?? '-'} • ${formatDateTime(evidence['created_at'])}',
                                      style: const TextStyle(fontSize: 12),
                                    ),
                                  );
                                })
                                .toList(),
                          ),
                  ),
                  const SizedBox(height: 20),

                  ModernCard(
                    title: 'Submit Final Result',
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.stretch,
                      children: [
                        DropdownButtonFormField<String>(
                          value: _finalResult,
                          decoration: const InputDecoration(
                            labelText: 'Final Result',
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'normal',
                              child: Text('Normal'),
                            ),
                            DropdownMenuItem(
                              value: 'abnormal',
                              child: Text('Abnormal'),
                            ),
                          ],
                          onChanged: (v) {
                            if (v != null) setState(() => _finalResult = v);
                          },
                        ),
                        const SizedBox(height: 12),
                        TextField(
                          controller: _summaryController,
                          maxLines: 3,
                          decoration: const InputDecoration(
                            labelText: 'Summary Notes',
                          ),
                        ),
                        if (_finalResult == 'abnormal') ...<Widget>[
                          const SizedBox(height: 16),
                          Row(
                            children: <Widget>[
                              FilledButton.tonalIcon(
                                onPressed: _submitting
                                    ? null
                                    : _pickSupportingFiles,
                                icon: const Icon(Icons.attach_file_rounded),
                                label: const Text('File Pendukung'),
                              ),
                              const SizedBox(width: 12),
                              Text('${_supportingFiles.length} file dipilih'),
                            ],
                          ),
                          if (_supportingFiles.isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(top: 12),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: _supportingFiles
                                    .map(
                                      (f) => Text(
                                        '• ${f.name}',
                                        style: const TextStyle(
                                          color: AppColors.textSecondary,
                                        ),
                                      ),
                                    )
                                    .toList(),
                              ),
                            ),
                        ],
                        const SizedBox(height: 24),
                        FilledButton.icon(
                          onPressed: _submitting ? null : _submitInspection,
                          icon: const Icon(Icons.check_circle_outline_rounded),
                          label: const Text('Submit Inspection'),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 40),
                ],
              ),
            ),
    );
  }
}

class InspectionResultsPage extends StatefulWidget {
  const InspectionResultsPage({super.key, required this.api});
  final ApiRepository api;
  @override
  State<InspectionResultsPage> createState() => _InspectionResultsPageState();
}

class _InspectionResultsPageState extends State<InspectionResultsPage> {
  bool _loading = true;
  List<Map<String, dynamic>> _items = <Map<String, dynamic>>[];
  Map<String, dynamic> _summary = <String, dynamic>{};
  int _page = 1;
  int _lastPage = 1;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load({int page = 1}) async {
    setState(() => _loading = true);
    try {
      final response = await widget.api.getInspectionResults(page: page);
      setState(() {
        _items = response.data;
        _summary =
            response.raw['summary'] as Map<String, dynamic>? ??
            <String, dynamic>{};
        _page = response.currentPage;
        _lastPage = response.lastPage;
      });
    } on ApiException catch (error) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: () => _load(page: _page),
      child: _loading
          ? const Center(child: CircularProgressIndicator())
          : ListView(
              padding: const EdgeInsets.all(20),
              children: <Widget>[
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: AppStyles.cardBorderRadius,
                    boxShadow: AppStyles.cardShadow,
                  ),
                  child: Wrap(
                    spacing: 12,
                    runSpacing: 12,
                    children: <Widget>[
                      StatusBadge(label: 'Total: ${_summary['total'] ?? 0}'),
                      StatusBadge(label: 'Normal: ${_summary['normal'] ?? 0}'),
                      StatusBadge(
                        label: 'Abnormal: ${_summary['abnormal'] ?? 0}',
                      ),
                      StatusBadge(
                        label: 'Tickets: ${_summary['with_ticket'] ?? 0}',
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                ..._items.map(
                  (row) => Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: AppStyles.cardBorderRadius,
                        boxShadow: AppStyles.cardShadow,
                      ),
                      child: ListTile(
                        contentPadding: const EdgeInsets.all(16),
                        title: Text(
                          '${row['inspection_number'] ?? '-'} • ${row['asset_name'] ?? '-'}',
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Padding(
                          padding: const EdgeInsets.only(top: 8),
                          child: Text(
                            '${row['inspection_officer_name'] ?? '-'} • ${formatDate(row['inspection_date'])}\nFinal: ${row['final_result'] ?? '-'} • Ticket: ${row['linked_ticket_number'] ?? '-'}',
                            style: const TextStyle(height: 1.4),
                          ),
                        ),
                        trailing: StatusBadge(
                          label: (row['status'] as String?) ?? '-',
                        ),
                      ),
                    ),
                  ),
                ),
                PaginationBar(
                  page: _page,
                  lastPage: _lastPage,
                  onPrev: _page > 1 ? () => _load(page: _page - 1) : null,
                  onNext: _page < _lastPage
                      ? () => _load(page: _page + 1)
                      : null,
                ),
              ],
            ),
    );
  }
}

class MobileNotificationsPage extends StatefulWidget {
  const MobileNotificationsPage({super.key, required this.api});

  final ApiRepository api;

  @override
  State<MobileNotificationsPage> createState() =>
      _MobileNotificationsPageState();
}

class _MobileNotificationsPageState extends State<MobileNotificationsPage> {
  bool _loading = true;
  List<Map<String, dynamic>> _notifications = <Map<String, dynamic>>[];

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() => _loading = true);

    try {
      final notifResp = await widget.api.getMobileNotifications(limit: 40);

      setState(() {
        _notifications = (notifResp['data'] as List<dynamic>? ?? <dynamic>[])
            .whereType<Map<String, dynamic>>()
            .toList();
      });
    } on ApiException catch (error) {
      if (!mounted) {
        return;
      }
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(error.message)));
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Notifications')),
      body: RefreshIndicator(
        onRefresh: _load,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : ListView(
                padding: const EdgeInsets.all(20),
                children: <Widget>[
                  ModernCard(
                    title: 'Push Notifications',
                    child: Column(
                      children: [
                        const InfoTile(
                          label: 'Mode',
                          value: 'Auto-registered on device login',
                        ),
                        const InfoTile(
                          label: 'Source',
                          value: 'Firebase Messaging Token',
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 20),
                  ModernCard(
                    title: 'Recent Notifications',
                    child: _notifications.isEmpty
                        ? const Text('Belum ada notifikasi.')
                        : Column(
                            children: _notifications
                                .map(
                                  (item) => Container(
                                    margin: const EdgeInsets.only(bottom: 10),
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: AppColors.background,
                                      borderRadius: BorderRadius.circular(12),
                                      border: Border.all(
                                        color: AppColors.border,
                                      ),
                                    ),
                                    child: Row(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Icon(
                                          (item['type'] as String?) ==
                                                  'inspection'
                                              ? Icons.fact_check_rounded
                                              : Icons
                                                    .confirmation_number_rounded,
                                          color: AppColors.primary,
                                        ),
                                        const SizedBox(width: 10),
                                        Expanded(
                                          child: Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                item['title'] as String? ?? '-',
                                                style: const TextStyle(
                                                  fontWeight: FontWeight.w700,
                                                ),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                item['message'] as String? ??
                                                    '-',
                                                style: const TextStyle(
                                                  color:
                                                      AppColors.textSecondary,
                                                ),
                                              ),
                                              const SizedBox(height: 4),
                                              Text(
                                                formatDateTime(
                                                  item['occurred_at'],
                                                ),
                                                style: const TextStyle(
                                                  fontSize: 12,
                                                  color:
                                                      AppColors.textSecondary,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                )
                                .toList(),
                          ),
                  ),
                ],
              ),
      ),
    );
  }
}

class ProfilePage extends StatefulWidget {
  const ProfilePage({super.key, required this.api, required this.session});
  final ApiRepository api;
  final SessionController session;
  @override
  State<ProfilePage> createState() => _ProfilePageState();
}

class _ProfilePageState extends State<ProfilePage> {
  late final TextEditingController _nameController;
  late final TextEditingController _emailController;
  final TextEditingController _departmentIdController = TextEditingController();
  final TextEditingController _currentPasswordController =
      TextEditingController();
  final TextEditingController _newPasswordController = TextEditingController();
  final TextEditingController _confirmPasswordController =
      TextEditingController();
  bool _saving = false;

  @override
  void initState() {
    super.initState();
    final user = widget.session.user;
    _nameController = TextEditingController(text: user?.name ?? '');
    _emailController = TextEditingController(text: user?.email ?? '');
    _departmentIdController.text = user?.departmentId?.toString() ?? '';
  }

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _departmentIdController.dispose();
    _currentPasswordController.dispose();
    _newPasswordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _refreshProfile() async {
    try {
      final user = await widget.api.me();
      _nameController.text = user.name;
      _emailController.text = user.email;
      _departmentIdController.text = user.departmentId?.toString() ?? '';
    } on ApiException catch (e) {
      _showSnack(e.message);
    }
  }

  Future<void> _save() async {
    setState(() => _saving = true);
    try {
      final payload = <String, dynamic>{
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim(),
      };
      final deptId = int.tryParse(_departmentIdController.text.trim());
      if (deptId != null) payload['department_id'] = deptId;

      if (_newPasswordController.text.isNotEmpty) {
        payload['password'] = _newPasswordController.text;
        payload['password_confirmation'] = _confirmPasswordController.text;
        payload['current_password'] = _currentPasswordController.text;
      }
      await widget.api.updateMe(payload);
      _showSnack('Profile berhasil diperbarui.');
    } on ApiException catch (e) {
      _showSnack(e.errors.values.firstOrNull?.first ?? e.message);
    } finally {
      if (mounted) setState(() => _saving = false);
    }
  }

  void _showSnack(String message) => ScaffoldMessenger.of(
    context,
  ).showSnackBar(SnackBar(content: Text(message)));

  @override
  Widget build(BuildContext context) {
    return RefreshIndicator(
      onRefresh: _refreshProfile,
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: <Widget>[
          ModernCard(
            title: 'Informasi Pribadi',
            child: Column(
              children: [
                TextField(
                  controller: _nameController,
                  decoration: const InputDecoration(labelText: 'Nama Lengkap'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _emailController,
                  decoration: const InputDecoration(labelText: 'Email Address'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _departmentIdController,
                  keyboardType: TextInputType.number,
                  decoration: const InputDecoration(
                    labelText: 'Department ID (opsional)',
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          ModernCard(
            title: 'Keamanan',
            child: Column(
              children: [
                TextField(
                  controller: _currentPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'Current Password',
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _newPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(labelText: 'Password Baru'),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _confirmPasswordController,
                  obscureText: true,
                  decoration: const InputDecoration(
                    labelText: 'Konfirmasi Password Baru',
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          FilledButton.icon(
            onPressed: _saving ? null : _save,
            icon: _saving
                ? const SizedBox(
                    width: 16,
                    height: 16,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.save_rounded),
            label: const Text('Simpan Perubahan'),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// SHARED WIDGETS
// ==========================================
class HeaderLogoPlaceholder extends StatelessWidget {
  const HeaderLogoPlaceholder({super.key, this.size = 40});
  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(size * 0.3),
        gradient: const LinearGradient(
          colors: [AppColors.primary, AppColors.primaryLight],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        boxShadow: [
          BoxShadow(
            color: AppColors.primary.withOpacity(0.3),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Icon(Icons.hexagon_rounded, color: Colors.white, size: size * 0.6),
    );
  }
}

class ModernCard extends StatelessWidget {
  const ModernCard({
    super.key,
    required this.title,
    required this.child,
    this.action,
    this.padding,
  });
  final String title;
  final Widget child;
  final Widget? action;
  final EdgeInsetsGeometry? padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: AppStyles.cardBorderRadius,
        boxShadow: AppStyles.cardShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 16, 8),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: AppColors.textPrimary,
                  ),
                ),
                if (action != null) action!,
              ],
            ),
          ),
          Padding(
            padding: padding ?? const EdgeInsets.fromLTRB(20, 0, 20, 20),
            child: child,
          ),
        ],
      ),
    );
  }
}

class StatCard extends StatelessWidget {
  const StatCard({
    super.key,
    required this.title,
    required this.value,
    required this.subtitle,
    required this.icon,
    required this.color,
  });
  final String title;
  final String value;
  final String subtitle;
  final IconData icon;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: AppStyles.cardBorderRadius,
        boxShadow: AppStyles.cardShadow,
      ),
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: <Widget>[
          Row(
            children: <Widget>[
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: color.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 20, color: color),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  title,
                  style: const TextStyle(
                    color: AppColors.textSecondary,
                    fontWeight: FontWeight.w500,
                    fontSize: 13,
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const Spacer(),
          Text(
            value,
            style: const TextStyle(
              fontSize: 24,
              fontWeight: FontWeight.bold,
              color: AppColors.textPrimary,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 12,
              color: AppColors.textSecondary,
            ),
          ),
        ],
      ),
    );
  }
}

class MetricProgressRow extends StatelessWidget {
  const MetricProgressRow({
    super.key,
    required this.label,
    required this.valueLabel,
    required this.progress,
    required this.color,
  });
  final String label;
  final String valueLabel;
  final double progress;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: <Widget>[
        Row(
          children: <Widget>[
            Expanded(
              child: Text(
                label,
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
            ),
            Text(
              valueLabel,
              style: TextStyle(fontWeight: FontWeight.bold, color: color),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ClipRRect(
          borderRadius: BorderRadius.circular(999),
          child: LinearProgressIndicator(
            minHeight: 8,
            value: progress,
            backgroundColor: AppColors.background,
            valueColor: AlwaysStoppedAnimation<Color>(color),
          ),
        ),
      ],
    );
  }
}

class PaginationBar extends StatelessWidget {
  const PaginationBar({
    super.key,
    required this.page,
    required this.lastPage,
    this.onPrev,
    this.onNext,
  });
  final int page;
  final int lastPage;
  final VoidCallback? onPrev;
  final VoidCallback? onNext;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 16),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: <Widget>[
          IconButton(
            onPressed: onPrev,
            icon: const Icon(Icons.chevron_left_rounded),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Text(
              'Page $page of $lastPage',
              style: const TextStyle(
                fontWeight: FontWeight.w600,
                color: AppColors.textSecondary,
              ),
            ),
          ),
          IconButton(
            onPressed: onNext,
            icon: const Icon(Icons.chevron_right_rounded),
          ),
        ],
      ),
    );
  }
}

class StatusBadge extends StatelessWidget {
  const StatusBadge({super.key, required this.label, this.color});
  final String label;
  final Color? color;

  Color _getBgColor() {
    if (color != null) {
      return color!.withOpacity(0.12);
    }

    final l = label.toLowerCase();
    if (l.contains('pass') ||
        l.contains('complet') ||
        l.contains('normal') && !l.contains('ab'))
      return AppColors.successBg;
    if (l.contains('fail') || l.contains('abnormal')) return AppColors.errorBg;
    if (l.contains('progress') || l.contains('start')) return AppColors.infoBg;
    return AppColors.warningBg;
  }

  Color _getTextColor() {
    if (color != null) {
      return color!;
    }

    final l = label.toLowerCase();
    if (l.contains('pass') ||
        l.contains('complet') ||
        l.contains('normal') && !l.contains('ab'))
      return AppColors.success;
    if (l.contains('fail') || l.contains('abnormal')) return AppColors.error;
    if (l.contains('progress') || l.contains('start')) return AppColors.info;
    return AppColors.warning;
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: _getBgColor(),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label.toUpperCase(),
        style: TextStyle(
          color: _getTextColor(),
          fontSize: 10,
          fontWeight: FontWeight.bold,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}

class InfoTile extends StatelessWidget {
  const InfoTile({super.key, required this.label, required this.value});
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: <Widget>[
          SizedBox(
            width: 110,
            child: Text(
              label,
              style: const TextStyle(color: AppColors.textSecondary),
            ),
          ),
          const Text(': ', style: TextStyle(color: AppColors.textSecondary)),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontWeight: FontWeight.w500),
            ),
          ),
        ],
      ),
    );
  }
}

String formatDate(dynamic raw) {
  if (raw == null) return '-';
  final parsed = DateTime.tryParse(raw.toString());
  if (parsed == null) return raw.toString();
  return DateFormat('dd MMM yyyy').format(parsed);
}

String formatDateTime(dynamic raw) {
  if (raw == null) return '-';
  final parsed = DateTime.tryParse(raw.toString());
  if (parsed == null) return raw.toString();
  return DateFormat('dd MMM yyyy HH:mm').format(parsed.toLocal());
}

String formatDurationMinutes(dynamic raw) {
  final minutes = (raw as num?)?.toInt();
  if (minutes == null || minutes < 0) {
    return '-';
  }
  if (minutes < 60) {
    return '$minutes min';
  }

  final hours = minutes ~/ 60;
  final remainingMinutes = minutes % 60;
  if (remainingMinutes == 0) {
    return '${hours}h';
  }

  return '${hours}h ${remainingMinutes}m';
}

extension FirstOrNullExtension<T> on List<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
