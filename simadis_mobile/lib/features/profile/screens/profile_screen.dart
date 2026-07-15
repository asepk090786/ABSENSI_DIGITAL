import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../../core/theme/app_colors.dart';
import '../../../data/models/user_model.dart';
import '../../../data/repositories/profile_repository.dart';

final profileProvider = FutureProvider.autoDispose<UserModel>((ref) async {
  final repo = ref.watch(profileRepositoryProvider);
  return repo.getProfile();
});

class ProfileScreen extends ConsumerWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final profileAsync = ref.watch(profileProvider);
    final user = ref.watch(currentUserProvider);

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil'),
        actions: [
          IconButton(
            icon: const Icon(Icons.edit),
            onPressed: () => context.push('/profile/edit'),
          ),
        ],
      ),
      body: profileAsync.when(
        data: (profile) {
          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                const SizedBox(height: 24),
                CircleAvatar(
                  radius: 48,
                  backgroundColor: AppColors.primaryContainer,
                  child: Text(
                    (profile.name ?? 'U')[0].toUpperCase(),
                    style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: AppColors.primary),
                  ),
                ),
                const SizedBox(height: 16),
                Text(profile.name ?? '-', style: Theme.of(context).textTheme.headlineSmall),
                const SizedBox(height: 4),
                Text(profile.email ?? '-', style: Theme.of(context).textTheme.bodyMedium),
                const SizedBox(height: 4),
                Text('Role: ${(profile.roles ?? []).join(', ')}', style: Theme.of(context).textTheme.bodySmall),
                const SizedBox(height: 24),
                Card(
                  child: ListView(
                    shrinkWrap: true,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      ListTile(
                        leading: const Icon(Icons.person_outline),
                        title: const Text('Nama'),
                        subtitle: Text(profile.name ?? '-'),
                      ),
                      const Divider(),
                      ListTile(
                        leading: const Icon(Icons.alternate_email),
                        title: const Text('Username'),
                        subtitle: Text(profile.username ?? '-'),
                      ),
                      const Divider(),
                      ListTile(
                        leading: const Icon(Icons.email_outlined),
                        title: const Text('Email'),
                        subtitle: Text(profile.email ?? '-'),
                      ),
                      const Divider(),
                      ListTile(
                        leading: const Icon(Icons.lock_outline),
                        title: const Text('Ganti Password'),
                        trailing: const Icon(Icons.arrow_forward_ios, size: 16),
                        onTap: () => _showChangePasswordDialog(context, ref),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
        loading: () => const LoadingIndicator(),
        error: (e, _) => Center(
          child: EmptyStateWidget(
            icon: Icons.error_outline,
            title: 'Terjadi kesalahan',
            message: e.toString(),
            onRetry: () => ref.invalidate(profileProvider),
          ),
        ),
      ),
    );
  }

  void _showChangePasswordDialog(BuildContext context, WidgetRef ref) {
    final currentController = TextEditingController();
    final newController = TextEditingController();
    final confirmController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Ganti Password'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(controller: currentController, obscureText: true, decoration: const InputDecoration(labelText: 'Password Lama')),
            TextField(controller: newController, obscureText: true, decoration: const InputDecoration(labelText: 'Password Baru')),
            TextField(controller: confirmController, obscureText: true, decoration: const InputDecoration(labelText: 'Konfirmasi Password')),
          ],
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('Batal')),
          TextButton(
            onPressed: () async {
              if (newController.text != confirmController.text) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password tidak cocok')));
                return;
              }
              try {
                await ref.read(profileRepositoryProvider).changePassword(
                      currentController.text,
                      newController.text,
                    );
                Navigator.pop(context);
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Password berhasil diubah')));
                }
              } catch (e) {
                if (mounted) {
                  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Gagal: ${e.toString()}')));
                }
              }
            },
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }
}
