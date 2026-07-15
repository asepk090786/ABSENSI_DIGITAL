import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../features/auth/screens/login_screen.dart';
import '../features/auth/screens/splash_screen.dart';
import '../features/dashboard/screens/dashboard_screen.dart';
import '../features/classes/screens/class_list_screen.dart';
import '../features/classes/screens/class_detail_screen.dart';
import '../features/students/screens/student_list_screen.dart';
import '../features/teachers/screens/teacher_list_screen.dart';
import '../features/attendance/screens/attendance_list_screen.dart';
import '../features/attendance/screens/attendance_input_screen.dart';
import '../features/attendance/screens/attendance_recap_screen.dart';
import '../features/reports/screens/report_screen.dart';
import '../features/profile/screens/profile_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final isLoggedIn = ref.watch(isLoggedInProvider);

  return GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      if (state.location == '/splash') return null;
      if (!isLoggedIn) return '/login';
      return null;
    },
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/dashboard',
        builder: (context, state) => const DashboardScreen(),
      ),
      GoRoute(
        path: '/classes',
        builder: (context, state) => const ClassListScreen(),
      ),
      GoRoute(
        path: '/classes/:id',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return ClassDetailScreen(classId: id);
        },
      ),
      GoRoute(
        path: '/students',
        builder: (context, state) => const StudentListScreen(),
      ),
      GoRoute(
        path: '/teachers',
        builder: (context, state) => const TeacherListScreen(),
      ),
      GoRoute(
        path: '/attendance',
        builder: (context, state) => const AttendanceListScreen(),
      ),
      GoRoute(
        path: '/attendance/:id',
        builder: (context, state) {
          final id = int.parse(state.pathParameters['id']!);
          return AttendanceInputScreen(attendanceId: id);
        },
      ),
      GoRoute(
        path: '/attendance/rekap',
        builder: (context, state) => const AttendanceRecapScreen(),
      ),
      GoRoute(
        path: '/reports',
        builder: (context, state) => const ReportScreen(),
      ),
      GoRoute(
        path: '/profile',
        builder: (context, state) => const ProfileScreen(),
      ),
    ],
  );
});
