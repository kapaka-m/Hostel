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
        title: Text(title),
        actions: [
          IconButton(
            tooltip: themeProvider.isDark ? 'Switch to light mode' : 'Switch to dark mode',
            icon: Icon(themeProvider.isDark ? Icons.light_mode : Icons.dark_mode),
            onPressed: () => themeProvider.setMode(
              themeProvider.isDark ? ThemeMode.light : ThemeMode.dark,
            ),
          ),
          IconButton(
            tooltip: 'Sign out',
            icon: const Icon(Icons.logout),
            onPressed: () => auth.logout(),
          ),
          if (!useDrawer && isWide)
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Center(
                child: Text(
                  auth.user?.name ?? 'Guest',
                  style: Theme.of(context).textTheme.labelLarge,
                ),
              ),
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
              title: Text(auth.user?.name ?? 'Guest'),
              subtitle: Text(auth.user?.role ?? ''),
              leading: const Icon(Icons.account_circle),
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
}
