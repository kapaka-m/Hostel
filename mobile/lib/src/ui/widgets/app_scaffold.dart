import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/auth/auth_provider.dart';
import 'package:hostel_mobile/src/theme/theme_provider.dart';

class AppNavDestination {
  final String path;
  final String label;
  final IconData icon;

  const AppNavDestination({
    required this.path,
    required this.label,
    required this.icon,
  });
}

class AppScaffold extends StatelessWidget {
  final String title;
  final List<AppNavDestination> destinations;
  final String currentLocation;
  final Widget child;

  const AppScaffold({
    super.key,
    required this.title,
    required this.destinations,
    required this.currentLocation,
    required this.child,
  });

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final width = MediaQuery.of(context).size.width;
    final isWide = kIsWeb ? width >= 900 : width >= 900;
    final useDrawer = !isWide && destinations.length > 4;
    final selectedIndex = _selectedIndex();

    return Scaffold(
      appBar: AppBar(
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title),
            if ((auth.user?.role ?? '').isNotEmpty)
              Text(
                auth.user?.role ?? '',
                style: Theme.of(context).textTheme.labelSmall,
              ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: themeProvider.isDark ? 'Switch to light mode' : 'Switch to dark mode',
            icon: Icon(themeProvider.isDark ? Icons.light_mode : Icons.dark_mode),
            onPressed: () => themeProvider.setMode(
              themeProvider.isDark ? ThemeMode.light : ThemeMode.dark,
            ),
          ),
          if (!useDrawer && isWide)
            Padding(
              padding: const EdgeInsets.only(right: 8),
              child: Center(
                child: Row(
                  children: [
                    CircleAvatar(
                      radius: 16,
                      child: Text(_initials(auth.user?.name)),
                    ),
                    const SizedBox(width: 8),
                    Text(auth.user?.name ?? 'Guest'),
                  ],
                ),
              ),
            ),
          IconButton(
            tooltip: 'Sign out',
            icon: const Icon(Icons.logout),
            onPressed: () => auth.logout(),
          ),
        ],
      ),
      drawer: useDrawer ? _buildDrawer(context) : null,
      body: Row(
        children: [
          if (isWide)
            NavigationRail(
              selectedIndex: selectedIndex,
              onDestinationSelected: (index) => _go(context, destinations[index].path),
              labelType: NavigationRailLabelType.all,
              destinations: destinations
                  .map((destination) => NavigationRailDestination(
                        icon: Icon(destination.icon),
                        label: Text(destination.label),
                      ))
                  .toList(),
            ),
          Expanded(child: child),
        ],
      ),
      bottomNavigationBar: isWide || useDrawer
          ? null
          : NavigationBar(
              selectedIndex: selectedIndex,
              onDestinationSelected: (index) => _go(context, destinations[index].path),
              destinations: destinations
                  .map((destination) => NavigationDestination(
                        icon: Icon(destination.icon),
                        label: destination.label,
                      ))
                  .toList(),
            ),
    );
  }

  Drawer _buildDrawer(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    return Drawer(
      child: SafeArea(
        child: ListView(
          padding: EdgeInsets.zero,
          children: [
            ListTile(
              leading: CircleAvatar(child: Text(_initials(auth.user?.name))),
              title: Text(auth.user?.name ?? 'Guest'),
              subtitle: Text(auth.user?.role ?? ''),
            ),
            const Divider(),
            ...destinations.map((destination) => ListTile(
                  leading: Icon(destination.icon),
                  title: Text(destination.label),
                  selected: currentLocation.startsWith(destination.path),
                  onTap: () {
                    Navigator.of(context).pop();
                    _go(context, destination.path);
                  },
                )),
          ],
        ),
      ),
    );
  }

  int _selectedIndex() {
    for (var index = 0; index < destinations.length; index++) {
      if (currentLocation.startsWith(destinations[index].path)) {
        return index;
      }
    }
    return 0;
  }

  void _go(BuildContext context, String path) {
    if (GoRouter.of(context).location != path) {
      context.go(path);
    }
  }

  String _initials(String? name) {
    if (name == null || name.trim().isEmpty) {
      return 'U';
    }
    final parts = name.trim().split(RegExp(r'\s+'));
    if (parts.length == 1) {
      return parts.first.substring(0, 1).toUpperCase();
    }
    return (parts[0].substring(0, 1) + parts[1].substring(0, 1)).toUpperCase();
  }
}
