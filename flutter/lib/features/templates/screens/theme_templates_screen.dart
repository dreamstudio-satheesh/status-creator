import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class ThemeTemplatesScreen extends StatefulWidget {
  final String themeId;
  final String themeName;

  const ThemeTemplatesScreen({
    Key? key,
    required this.themeId,
    required this.themeName,
  }) : super(key: key);

  @override
  State<ThemeTemplatesScreen> createState() => _ThemeTemplatesScreenState();
}

class _ThemeTemplatesScreenState extends State<ThemeTemplatesScreen> {
  final ScrollController _scrollController = ScrollController();
  final List<ThemeTemplate> _templates = List.generate(
    15,
    (index) => ThemeTemplate(
      id: index.toString(),
      title: 'Template ${index + 1}',
      isPremium: index % 3 == 0,
      downloads: (index + 1) * 100,
    ),
  );

  bool _isLoading = false;
  String _sortBy = 'Popular';

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels ==
        _scrollController.position.maxScrollExtent) {
      _loadMoreTemplates();
    }
  }

  void _loadMoreTemplates() async {
    if (_isLoading) return;
    
    setState(() => _isLoading = true);
    
    // Simulate loading more templates
    await Future.delayed(const Duration(seconds: 1));
    
    if (mounted) {
      setState(() {
        _templates.addAll(List.generate(
          10,
          (index) => ThemeTemplate(
            id: (_templates.length + index).toString(),
            title: 'Template ${_templates.length + index + 1}',
            isPremium: (_templates.length + index) % 3 == 0,
            downloads: (_templates.length + index + 1) * 50,
          ),
        ));
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.themeName),
        actions: [
          PopupMenuButton<String>(
            icon: const Icon(Icons.sort),
            onSelected: (value) {
              setState(() {
                _sortBy = value;
                // TODO: Implement sorting
              });
            },
            itemBuilder: (context) => [
              const PopupMenuItem(
                value: 'Popular',
                child: Text('Popular'),
              ),
              const PopupMenuItem(
                value: 'Recent',
                child: Text('Recent'),
              ),
              const PopupMenuItem(
                value: 'Downloads',
                child: Text('Most Downloads'),
              ),
              const PopupMenuItem(
                value: 'Name',
                child: Text('Name A-Z'),
              ),
            ],
          ),
        ],
      ),
      body: Column(
        children: [
          // Theme Header
          _buildThemeHeader(theme),
          
          // Templates Grid
          Expanded(
            child: RefreshIndicator(
              onRefresh: () async {
                // TODO: Implement refresh
                await Future.delayed(const Duration(seconds: 1));
              },
              child: GridView.builder(
                controller: _scrollController,
                padding: const EdgeInsets.all(16),
                gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                  crossAxisCount: 2,
                  crossAxisSpacing: 12,
                  mainAxisSpacing: 12,
                  childAspectRatio: 0.75,
                ),
                itemCount: _templates.length + (_isLoading ? 2 : 0),
                itemBuilder: (context, index) {
                  if (index >= _templates.length) {
                    return _buildLoadingCard(theme);
                  }
                  return _buildTemplateCard(theme, _templates[index]);
                },
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildThemeHeader(ThemeData theme) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: theme.colorScheme.primary.withOpacity(0.1),
        border: Border(
          bottom: BorderSide(
            color: theme.colorScheme.outline.withOpacity(0.2),
          ),
        ),
      ),
      child: Column(
        children: [
          Icon(
            _getThemeIcon(widget.themeName),
            size: 48,
            color: theme.colorScheme.primary,
          ),
          const SizedBox(height: 8),
          Text(
            widget.themeName,
            style: theme.textTheme.headlineSmall?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '${_templates.length} templates available',
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onSurface.withOpacity(0.7),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                Icons.sort,
                size: 16,
                color: theme.colorScheme.onSurface.withOpacity(0.7),
              ),
              const SizedBox(width: 4),
              Text(
                'Sorted by $_sortBy',
                style: theme.textTheme.bodySmall?.copyWith(
                  color: theme.colorScheme.onSurface.withOpacity(0.7),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTemplateCard(ThemeData theme, ThemeTemplate template) {
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
                      color: theme.colorScheme.surfaceVariant,
                    ),
                    child: Icon(
                      Icons.image,
                      size: 48,
                      color: theme.colorScheme.onSurfaceVariant,
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
                            const Icon(
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
                  
                  // Quick Actions
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
                              Icons.edit,
                              size: 16,
                              color: Colors.white,
                            ),
                            onPressed: () {
                              context.go('/editor', extra: {
                                'templateId': template.id,
                                'isEdit': false,
                              });
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
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      template.title,
                      style: theme.textTheme.titleSmall?.copyWith(
                        fontWeight: FontWeight.w600,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    Row(
                      children: [
                        Icon(
                          Icons.download,
                          size: 12,
                          color: theme.colorScheme.onSurface.withOpacity(0.7),
                        ),
                        const SizedBox(width: 2),
                        Text(
                          _formatDownloads(template.downloads),
                          style: theme.textTheme.bodySmall?.copyWith(
                            color: theme.colorScheme.onSurface.withOpacity(0.7),
                          ),
                        ),
                      ],
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

  Widget _buildLoadingCard(ThemeData theme) {
    return Card(
      child: Container(
        decoration: BoxDecoration(
          color: theme.colorScheme.surfaceVariant.withOpacity(0.3),
          borderRadius: BorderRadius.circular(12),
        ),
        child: const Center(
          child: CircularProgressIndicator(),
        ),
      ),
    );
  }

  IconData _getThemeIcon(String themeName) {
    switch (themeName.toLowerCase()) {
      case 'love':
        return Icons.favorite;
      case 'motivation':
        return Icons.psychology;
      case 'friendship':
        return Icons.people;
      case 'success':
        return Icons.emoji_events;
      case 'life':
        return Icons.wb_sunny;
      case 'family':
        return Icons.family_restroom;
      default:
        return Icons.format_quote;
    }
  }

  String _formatDownloads(int downloads) {
    if (downloads >= 1000) {
      return '${(downloads / 1000).toStringAsFixed(1)}K';
    }
    return downloads.toString();
  }
}

class ThemeTemplate {
  final String id;
  final String title;
  final bool isPremium;
  final int downloads;

  ThemeTemplate({
    required this.id,
    required this.title,
    this.isPremium = false,
    required this.downloads,
  });
}