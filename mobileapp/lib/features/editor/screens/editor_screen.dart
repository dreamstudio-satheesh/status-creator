import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class EditorScreen extends StatefulWidget {
  final String? templateId;
  final String? imageUrl;
  final bool isEdit;

  const EditorScreen({
    Key? key,
    this.templateId,
    this.imageUrl,
    this.isEdit = false,
  }) : super(key: key);

  @override
  State<EditorScreen> createState() => _EditorScreenState();
}

class _EditorScreenState extends State<EditorScreen> with TickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _textController = TextEditingController();
  
  // Editor state
  String _selectedFont = 'Inter';
  double _fontSize = 18.0;
  Color _textColor = Colors.white;
  Color _backgroundColor = Colors.blue;
  TextAlign _textAlign = TextAlign.center;
  bool _isBold = false;
  bool _isItalic = false;
  bool _isGeneratingAI = false;

  final List<String> _fonts = [
    'Inter',
    'Roboto',
    'Open Sans',
    'Lato',
    'Poppins',
  ];

  final List<Color> _colors = [
    Colors.white,
    Colors.black,
    Colors.red,
    Colors.blue,
    Colors.green,
    Colors.orange,
    Colors.purple,
    Colors.pink,
  ];

  final List<Color> _backgroundColors = [
    Colors.blue,
    Colors.purple,
    Colors.green,
    Colors.orange,
    Colors.red,
    Colors.pink,
    Colors.teal,
    Colors.indigo,
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    
    if (widget.templateId != null) {
      _textController.text = 'Sample Tamil quote for template ${widget.templateId}';
    }
  }

  @override
  void dispose() {
    _tabController.dispose();
    _textController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.isEdit ? 'Edit Status' : 'Create Status'),
        actions: [
          IconButton(
            icon: const Icon(Icons.save),
            onPressed: _saveStatus,
          ),
          IconButton(
            icon: const Icon(Icons.share),
            onPressed: _shareStatus,
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(icon: Icon(Icons.text_fields), text: 'Text'),
            Tab(icon: Icon(Icons.palette), text: 'Style'),
            Tab(icon: Icon(Icons.auto_awesome), text: 'AI'),
            Tab(icon: Icon(Icons.image), text: 'Background'),
          ],
        ),
      ),
      body: Column(
        children: [
          // Preview Area
          Expanded(
            flex: 2,
            child: _buildPreviewArea(theme),
          ),
          
          // Editor Controls
          Expanded(
            flex: 1,
            child: TabBarView(
              controller: _tabController,
              children: [
                _buildTextTab(theme),
                _buildStyleTab(theme),
                _buildAITab(theme),
                _buildBackgroundTab(theme),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildPreviewArea(ThemeData theme) {
    return Container(
      margin: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: _backgroundColor,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.1),
            blurRadius: 10,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Text(
            _textController.text.isEmpty ? 'Your text will appear here' : _textController.text,
            style: TextStyle(
              fontFamily: _selectedFont,
              fontSize: _fontSize,
              color: _textColor,
              fontWeight: _isBold ? FontWeight.bold : FontWeight.normal,
              fontStyle: _isItalic ? FontStyle.italic : FontStyle.normal,
            ),
            textAlign: _textAlign,
          ),
        ),
      ),
    );
  }

  Widget _buildTextTab(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Enter your text',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          
          // Text Input
          Expanded(
            child: TextField(
              controller: _textController,
              maxLines: null,
              expands: true,
              textAlignVertical: TextAlignVertical.top,
              decoration: const InputDecoration(
                hintText: 'Type your Tamil status here...',
                border: OutlineInputBorder(),
              ),
              onChanged: (value) {
                setState(() {});
              },
            ),
          ),
          
          const SizedBox(height: 12),
          
          // Text Alignment
          Row(
            children: [
              Text(
                'Alignment: ',
                style: theme.textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.w500,
                ),
              ),
              const SizedBox(width: 8),
              SegmentedButton<TextAlign>(
                segments: const [
                  ButtonSegment(
                    value: TextAlign.left,
                    icon: Icon(Icons.format_align_left),
                  ),
                  ButtonSegment(
                    value: TextAlign.center,
                    icon: Icon(Icons.format_align_center),
                  ),
                  ButtonSegment(
                    value: TextAlign.right,
                    icon: Icon(Icons.format_align_right),
                  ),
                ],
                selected: {_textAlign},
                onSelectionChanged: (Set<TextAlign> selection) {
                  setState(() {
                    _textAlign = selection.first;
                  });
                },
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStyleTab(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Font Selection
          Text(
            'Font',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: _selectedFont,
            decoration: const InputDecoration(
              border: OutlineInputBorder(),
            ),
            items: _fonts.map((font) => DropdownMenuItem(
              value: font,
              child: Text(font, style: TextStyle(fontFamily: font)),
            )).toList(),
            onChanged: (value) {
              setState(() {
                _selectedFont = value!;
              });
            },
          ),
          
          const SizedBox(height: 16),
          
          // Font Size
          Text(
            'Font Size: ${_fontSize.toInt()}',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          Slider(
            value: _fontSize,
            min: 12,
            max: 48,
            divisions: 36,
            onChanged: (value) {
              setState(() {
                _fontSize = value;
              });
            },
          ),
          
          const SizedBox(height: 16),
          
          // Text Style Options
          Row(
            children: [
              FilterChip(
                label: const Text('Bold'),
                selected: _isBold,
                onSelected: (selected) {
                  setState(() {
                    _isBold = selected;
                  });
                },
              ),
              const SizedBox(width: 8),
              FilterChip(
                label: const Text('Italic'),
                selected: _isItalic,
                onSelected: (selected) {
                  setState(() {
                    _isItalic = selected;
                  });
                },
              ),
            ],
          ),
          
          const SizedBox(height: 16),
          
          // Text Color
          Text(
            'Text Color',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 50,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              itemCount: _colors.length,
              itemBuilder: (context, index) {
                final color = _colors[index];
                return GestureDetector(
                  onTap: () {
                    setState(() {
                      _textColor = color;
                    });
                  },
                  child: Container(
                    width: 50,
                    height: 50,
                    margin: const EdgeInsets.only(right: 8),
                    decoration: BoxDecoration(
                      color: color,
                      border: Border.all(
                        color: _textColor == color
                            ? theme.colorScheme.primary
                            : Colors.grey,
                        width: _textColor == color ? 3 : 1,
                      ),
                      borderRadius: BorderRadius.circular(8),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAITab(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'AI Quote Generator',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Generate beautiful Tamil quotes using AI',
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onSurface.withOpacity(0.7),
            ),
          ),
          
          const SizedBox(height: 24),
          
          // AI Generation Options
          const Text('Choose a theme:'),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: ['Love', 'Motivation', 'Life', 'Success', 'Friendship']
                .map((theme) => ChoiceChip(
                      label: Text(theme),
                      selected: false,
                      onSelected: (selected) {
                        if (selected) {
                          _generateAIQuote(theme);
                        }
                      },
                    ))
                .toList(),
          ),
          
          const SizedBox(height: 24),
          
          // Generate Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: _isGeneratingAI ? null : () => _generateAIQuote('General'),
              icon: _isGeneratingAI
                  ? const SizedBox(
                      width: 16,
                      height: 16,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    )
                  : const Icon(Icons.auto_awesome),
              label: Text(_isGeneratingAI ? 'Generating...' : 'Generate AI Quote'),
            ),
          ),
          
          const SizedBox(height: 16),
          
          // Premium Notice
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.orange.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: Colors.orange.withOpacity(0.3)),
            ),
            child: Row(
              children: [
                const Icon(Icons.star, color: Colors.orange, size: 20),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Premium feature: 5 AI generations remaining today',
                    style: theme.textTheme.bodySmall?.copyWith(
                      color: Colors.orange[700],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBackgroundTab(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Background Color',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          
          // Background Colors
          GridView.builder(
            shrinkWrap: true,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
            ),
            itemCount: _backgroundColors.length,
            itemBuilder: (context, index) {
              final color = _backgroundColors[index];
              return GestureDetector(
                onTap: () {
                  setState(() {
                    _backgroundColor = color;
                  });
                },
                child: Container(
                  decoration: BoxDecoration(
                    color: color,
                    border: Border.all(
                      color: _backgroundColor == color
                          ? theme.colorScheme.primary
                          : Colors.grey,
                      width: _backgroundColor == color ? 3 : 1,
                    ),
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
              );
            },
          ),
          
          const SizedBox(height: 24),
          
          // Gradient Option
          OutlinedButton.icon(
            onPressed: () {
              // TODO: Implement gradient picker
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Gradient picker coming soon!')),
              );
            },
            icon: const Icon(Icons.gradient),
            label: const Text('Choose Gradient'),
          ),
          
          const SizedBox(height: 12),
          
          // Background Image Option
          OutlinedButton.icon(
            onPressed: () {
              // TODO: Implement image picker
              ScaffoldMessenger.of(context).showSnackBar(
                const SnackBar(content: Text('Background image picker coming soon!')),
              );
            },
            icon: const Icon(Icons.image),
            label: const Text('Upload Background Image'),
          ),
        ],
      ),
    );
  }

  void _generateAIQuote(String theme) async {
    setState(() => _isGeneratingAI = true);
    
    try {
      // Simulate AI generation
      await Future.delayed(const Duration(seconds: 3));
      
      final quotes = {
        'Love': 'காதல் என்பது இரு இதயங்களின் இணைப்பு',
        'Motivation': 'வெற்றி என்பது விழுந்து எழுவதில் இருக்கிறது',
        'Life': 'வாழ்க்கை என்பது ஒரு அழகான பயணம்',
        'Success': 'முயற்சியே வெற்றியின் திறவுகோல்',
        'Friendship': 'நட்பு என்பது வாழ்க்கையின் அழகு',
        'General': 'நம்பிக்கையே நம் வலிமை',
      };
      
      if (mounted) {
        setState(() {
          _textController.text = quotes[theme] ?? quotes['General']!;
          _isGeneratingAI = false;
        });
      }
    } catch (e) {
      if (mounted) {
        setState(() => _isGeneratingAI = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Failed to generate quote: ${e.toString()}'),
            backgroundColor: Theme.of(context).colorScheme.error,
          ),
        );
      }
    }
  }

  void _saveStatus() async {
    if (_textController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter some text')),
      );
      return;
    }

    // TODO: Implement save logic
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Status saved successfully!')),
    );
    
    context.go('/creations');
  }

  void _shareStatus() {
    if (_textController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please enter some text')),
      );
      return;
    }

    // TODO: Implement share logic
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(content: Text('Share feature coming soon!')),
    );
  }
}