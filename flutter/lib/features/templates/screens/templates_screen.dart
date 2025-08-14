import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class TemplatesScreen extends ConsumerStatefulWidget {
  const TemplatesScreen({Key? key}) : super(key: key);

  @override
  ConsumerState<TemplatesScreen> createState() => _TemplatesScreenState();
}

class _TemplatesScreenState extends ConsumerState<TemplatesScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();
  
  final List<String> _categories = [
    'All',
    'Love',
    'Motivation',
    'Friendship',
    'Success',
    'Life',
    'Family',
    'Wisdom',
    'Inspiration',
  ];

  final List<TemplateItem> _templates = List.generate(
    20,
    (index) => TemplateItem(
      id: index.toString(),
      title: 'Template ${index + 1}',
      category: ['Love', 'Motivation', 'Friendship'][index % 3],
      isPremium: index % 4 == 0,
    ),
  );

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _categories.length, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  List<TemplateItem> get _filteredTemplates {
    final selectedCategory = _categories[_tabController.index];
    if (selectedCategory == 'All') {
      return _templates;
    }
    return _templates.where((template) => template.category == selectedCategory).toList();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Templates'),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(100),
          child: Column(
            children: [
              // Search Bar
              Padding(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                child: TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'Search templates...',
                    prefixIcon: const Icon(Icons.search),
                    suffixIcon: IconButton(
                      icon: const Icon(Icons.filter_list),
                      onPressed: _showFilterBottomSheet,
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    filled: true,
                    fillColor: theme.colorScheme.surface,
                  ),
                  onChanged: (value) {
                    // TODO: Implement search
                  },
                ),
              ),
              
              // Category Tabs
              TabBar(
                controller: _tabController,
                isScrollable: true,
                tabs: _categories.map((category) => Tab(text: category)).toList(),
                onTap: (index) {
                  setState(() {});
                },
              ),
            ],
          ),
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: _categories.map((category) {
          return _buildTemplateGrid(theme);
        }).toList(),
      ),
    );
  }

  Widget _buildTemplateGrid(ThemeData theme) {
    final filteredTemplates = _filteredTemplates;
    
    if (filteredTemplates.isEmpty) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.search_off,
              size: 64,
              color: theme.colorScheme.onSurface.withOpacity(0.5),
            ),
            const SizedBox(height: 16),
            Text(
              'No templates found',
              style: theme.textTheme.titleMedium?.copyWith(
                color: theme.colorScheme.onSurface.withOpacity(0.7),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () async {
        // TODO: Implement refresh
        await Future.delayed(const Duration(seconds: 1));
      },
      child: GridView.builder(
        padding: const EdgeInsets.all(16),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: 0.75,
        ),
        itemCount: filteredTemplates.length,
        itemBuilder: (context, index) {
          return _buildTemplateCard(theme, filteredTemplates[index]);
        },
      ),
    );
  }

  Widget _buildTemplateCard(ThemeData theme, TemplateItem template) {
    return Card(
      clipBehavior: Clip.antiAlias,
      child: InkWell(
        onTap: () {
          context.go('/templates/details/${template.id}');
        },
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Template Image
            Expanded(
              flex: 3,
              child: Stack(
                fit: StackFit.expand,
                children: [
                  Container(
                    decoration: BoxDecoration(
                      color: theme.colorScheme.primary.withOpacity(0.1),
                    ),
                    child: Icon(
                      Icons.image,
                      size: 48,
                      color: theme.colorScheme.primary,
                    ),
                  ),
                  
                  // Premium Badge
                  if (template.isPremium)
                    Positioned(
                      top: 8,
                      right: 8,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.orange,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              Icons.star,
                              size: 12,
                              color: Colors.white,
                            ),
                            const SizedBox(width: 2),
                            Text(
                              'PRO',
                              style: theme.textTheme.bodySmall?.copyWith(
                                color: Colors.white,
                                fontSize: 10,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  
                  // Action Buttons
                  Positioned(
                    bottom: 8,
                    right: 8,
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: Colors.black.withOpacity(0.5),
                          child: IconButton(
                            icon: const Icon(
                              Icons.favorite_border,
                              size: 16,
                              color: Colors.white,
                            ),
                            onPressed: () {
                              // TODO: Add to favorites
                            },
                          ),
                        ),
                        const SizedBox(width: 4),
                        CircleAvatar(
                          radius: 16,
                          backgroundColor: Colors.black.withOpacity(0.5),
                          child: IconButton(
                            icon: const Icon(
                              Icons.download,
                              size: 16,
                              color: Colors.white,
                            ),
                            onPressed: () {
                              // TODO: Download template
                            },
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            
            // Template Info
            Expanded(
              flex: 1,
              child: Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      template.title,
                      style: theme.textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      template.category,
                      style: theme.textTheme.bodySmall?.copyWith(
                        color: theme.colorScheme.onSurface.withOpacity(0.7),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showFilterBottomSheet() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (context) {
        return DraggableScrollableSheet(
          initialChildSize: 0.5,
          minChildSize: 0.3,
          maxChildSize: 0.8,
          expand: false,
          builder: (context, scrollController) {
            return FilterBottomSheet(scrollController: scrollController);
          },
        );
      },
    );
  }
}

class TemplateItem {
  final String id;
  final String title;
  final String category;
  final bool isPremium;

  TemplateItem({
    required this.id,
    required this.title,
    required this.category,
    this.isPremium = false,
  });
}

class FilterBottomSheet extends StatefulWidget {
  final ScrollController scrollController;

  const FilterBottomSheet({Key? key, required this.scrollController}) : super(key: key);

  @override
  State<FilterBottomSheet> createState() => _FilterBottomSheetState();
}

class _FilterBottomSheetState extends State<FilterBottomSheet> {
  String _sortBy = 'Popular';
  bool _premiumOnly = false;
  RangeValues _popularityRange = const RangeValues(0, 100);

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Container(
      padding: const EdgeInsets.all(20),
      child: ListView(
        controller: widget.scrollController,
        children: [
          // Handle
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: theme.colorScheme.onSurface.withOpacity(0.3),
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          
          const SizedBox(height: 20),
          
          Text(
            'Filter Templates',
            style: theme.textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Sort By
          Text(
            'Sort By',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: _sortBy,
            decoration: const InputDecoration(
              border: OutlineInputBorder(),
            ),
            items: ['Popular', 'Recent', 'Most Used', 'A-Z']
                .map((sort) => DropdownMenuItem(
                      value: sort,
                      child: Text(sort),
                    ))
                .toList(),
            onChanged: (value) {
              setState(() {
                _sortBy = value!;
              });
            },
          ),
          
          const SizedBox(height: 20),
          
          // Premium Only Toggle
          SwitchListTile(
            title: const Text('Premium Templates Only'),
            subtitle: const Text('Show only premium templates'),
            value: _premiumOnly,
            onChanged: (value) {
              setState(() {
                _premiumOnly = value;
              });
            },
          ),
          
          const SizedBox(height: 20),
          
          // Popularity Range
          Text(
            'Popularity Range',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.w600,
            ),
          ),
          RangeSlider(
            values: _popularityRange,
            min: 0,
            max: 100,
            divisions: 10,
            labels: RangeLabels(
              _popularityRange.start.round().toString(),
              _popularityRange.end.round().toString(),
            ),
            onChanged: (values) {
              setState(() {
                _popularityRange = values;
              });
            },
          ),
          
          const SizedBox(height: 32),
          
          // Action Buttons
          Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () {
                    setState(() {
                      _sortBy = 'Popular';
                      _premiumOnly = false;
                      _popularityRange = const RangeValues(0, 100);
                    });
                  },
                  child: const Text('Reset'),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.pop(context);
                    // TODO: Apply filters
                  },
                  child: const Text('Apply'),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}