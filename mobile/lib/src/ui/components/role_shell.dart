import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:provider/provider.dart';

import 'package:hostel_mobile/src/features/auth/auth_provider.dart';
import 'package:hostel_mobile/src/ui/theme/theme_provider.dart';

class RoleDestination {
  final String path;
  final String label;
  final IconData icon;

  const RoleDestination({
    required this.path,
    required this.label,
    required this.icon,
  });
}

class RoleShell extends StatelessWidget {
  final Widget child;
  final List<RoleDestination> destinations;
  final String currentLocation;
  final String title;

  const RoleShell({
    super.key,
    required this.child,
    required this.destinations,
    required this.currentLocation,
    required this.title,
  });

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();
    final themeProvider = context.watch<ThemeProvider>();
    final isWide = kIsWeb || MediaQuery.of(context).size.width >= 800;
    final selectedIndex = _selectedIndex();

    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        actions: [
          IconButton(
            icon: Icon(themeProvider.isDark ? Icons.dark_mode : Icons.light_mode),
            onPressed: () => themeProvider.setMode(
              themeProvider.isDark ? ThemeMode.light : ThemeMode.dark,
            ),
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: () => auth.logout(),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16.0),
            child: Center(
              child: Text(
                auth.user?.name ?? 'Guest',
                style: Theme.of(context).textTheme.labelLarge,
              ),
            ),
          ),
        ],
      ),
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
      bottomNavigationBar: isWide
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
