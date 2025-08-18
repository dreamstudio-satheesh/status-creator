import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class SubscriptionScreen extends StatefulWidget {
  const SubscriptionScreen({Key? key}) : super(key: key);

  @override
  State<SubscriptionScreen> createState() => _SubscriptionScreenState();
}

class _SubscriptionScreenState extends State<SubscriptionScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  int _selectedPlanIndex = 1; // Monthly selected by default
  bool _isLoading = false;

  final List<SubscriptionPlan> _plans = [
    SubscriptionPlan(
      name: 'Weekly',
      price: 49,
      duration: 'week',
      savings: 0,
      features: [
        'Unlimited AI generations',
        'Premium templates',
        'HD image export',
        'No watermarks',
      ],
    ),
    SubscriptionPlan(
      name: 'Monthly',
      price: 149,
      duration: 'month',
      savings: 25,
      features: [
        'Unlimited AI generations',
        'Premium templates',
        'HD image export',
        'No watermarks',
        'Priority support',
        'Early access to new features',
      ],
    ),
    SubscriptionPlan(
      name: 'Yearly',
      price: 999,
      duration: 'year',
      savings: 65,
      features: [
        'Unlimited AI generations',
        'Premium templates',
        'HD image export',
        'No watermarks',
        'Priority support',
        'Early access to new features',
        'Exclusive premium content',
        'Custom watermarks',
      ],
    ),
  ];

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    
    return Scaffold(
      appBar: AppBar(
        title: const Text('Subscription'),
        bottom: TabBar(
          controller: _tabController,
          tabs: const [
            Tab(text: 'Plans'),
            Tab(text: 'Current'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildPlansTab(theme),
          _buildCurrentSubscriptionTab(theme),
        ],
      ),
    );
  }

  Widget _buildPlansTab(ThemeData theme) {
    return SingleChildScrollView(
      child: Column(
        children: [
          // Header
          _buildHeader(theme),
          
          // Plans
          _buildPlansSection(theme),
          
          // Features Comparison
          _buildFeaturesComparison(theme),
          
          // Subscribe Button
          _buildSubscribeButton(theme),
          
          // Footer
          _buildFooter(theme),
        ],
      ),
    );
  }

  Widget _buildCurrentSubscriptionTab(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Current Plan Card
          Card(
            child: Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Icon(
                        Icons.star,
                        color: Colors.orange,
                        size: 24,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Free Plan',
                        style: theme.textTheme.titleLarge?.copyWith(
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'You are currently on the free plan',
                    style: theme.textTheme.bodyMedium?.copyWith(
                      color: theme.colorScheme.onSurface.withOpacity(0.7),
                    ),
                  ),
                  const SizedBox(height: 16),
                  
                  // Usage Stats
                  _buildUsageItem(theme, 'AI Generations', '3/5', 'daily'),
                  _buildUsageItem(theme, 'Template Downloads', '12/20', 'daily'),
                  _buildUsageItem(theme, 'HD Exports', '0/2', 'daily'),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Upgrade Benefits
          Text(
            'Upgrade Benefits',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  _buildBenefitItem(theme, 'Unlimited AI generations'),
                  _buildBenefitItem(theme, 'Access to all premium templates'),
                  _buildBenefitItem(theme, 'HD image exports without limits'),
                  _buildBenefitItem(theme, 'Remove watermarks'),
                  _buildBenefitItem(theme, 'Priority customer support'),
                ],
              ),
            ),
          ),
          
          const SizedBox(height: 24),
          
          // Upgrade Button
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: () => _tabController.animateTo(0),
              style: ElevatedButton.styleFrom(
                backgroundColor: theme.colorScheme.primary,
                padding: const EdgeInsets.symmetric(vertical: 16),
              ),
              child: const Text(
                'Upgrade Now',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeader(ThemeData theme) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [
            theme.colorScheme.primary,
            theme.colorScheme.primary.withOpacity(0.8),
          ],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
      ),
      child: Column(
        children: [
          Icon(
            Icons.star,
            size: 48,
            color: theme.colorScheme.onPrimary,
          ),
          const SizedBox(height: 16),
          Text(
            'Unlock Premium Features',
            style: theme.textTheme.headlineMedium?.copyWith(
              color: theme.colorScheme.onPrimary,
              fontWeight: FontWeight.bold,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            'Create unlimited AI-powered Tamil status with premium templates and features',
            style: theme.textTheme.bodyMedium?.copyWith(
              color: theme.colorScheme.onPrimary.withOpacity(0.9),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildPlansSection(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Text(
            'Choose Your Plan',
            style: theme.textTheme.titleLarge?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 16),
          
          // Plans
          ...List.generate(_plans.length, (index) {
            return _buildPlanCard(theme, _plans[index], index);
          }),
        ],
      ),
    );
  }

  Widget _buildPlanCard(ThemeData theme, SubscriptionPlan plan, int index) {
    final isSelected = _selectedPlanIndex == index;
    final isPopular = index == 1; // Monthly plan is popular
    
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      child: Stack(
        children: [
          Card(
            elevation: isSelected ? 8 : 2,
            child: InkWell(
              onTap: () {
                setState(() {
                  _selectedPlanIndex = index;
                });
              },
              borderRadius: BorderRadius.circular(12),
              child: Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  border: isSelected 
                      ? Border.all(color: theme.colorScheme.primary, width: 2)
                      : null,
                ),
                child: Row(
                  children: [
                    // Radio Button
                    Radio<int>(
                      value: index,
                      groupValue: _selectedPlanIndex,
                      onChanged: (value) {
                        setState(() {
                          _selectedPlanIndex = value!;
                        });
                      },
                    ),
                    
                    const SizedBox(width: 12),
                    
                    // Plan Details
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                plan.name,
                                style: theme.textTheme.titleMedium?.copyWith(
                                  fontWeight: FontWeight.bold,
                                ),
                              ),
                              if (plan.savings > 0) ...[
                                const SizedBox(width: 8),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 8,
                                    vertical: 2,
                                  ),
                                  decoration: BoxDecoration(
                                    color: Colors.green,
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(
                                    'Save ${plan.savings}%',
                                    style: theme.textTheme.bodySmall?.copyWith(
                                      color: Colors.white,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                ),
                              ],
                            ],
                          ),
                          const SizedBox(height: 4),
                          RichText(
                            text: TextSpan(
                              style: theme.textTheme.bodyLarge,
                              children: [
                                TextSpan(
                                  text: '₹${plan.price}',
                                  style: TextStyle(
                                    fontSize: 24,
                                    fontWeight: FontWeight.bold,
                                    color: theme.colorScheme.primary,
                                  ),
                                ),
                                TextSpan(
                                  text: '/${plan.duration}',
                                  style: TextStyle(
                                    color: theme.colorScheme.onSurface.withOpacity(0.7),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
          
          // Popular Badge
          if (isPopular)
            Positioned(
              top: 0,
              right: 16,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.orange,
                  borderRadius: const BorderRadius.vertical(
                    bottom: Radius.circular(12),
                  ),
                ),
                child: Text(
                  'MOST POPULAR',
                  style: theme.textTheme.bodySmall?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildFeaturesComparison(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'What\'s Included',
            style: theme.textTheme.titleMedium?.copyWith(
              fontWeight: FontWeight.bold,
            ),
          ),
          const SizedBox(height: 12),
          
          Card(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: _plans[_selectedPlanIndex].features.map((feature) {
                  return Padding(
                    padding: const EdgeInsets.symmetric(vertical: 4),
                    child: Row(
                      children: [
                        Icon(
                          Icons.check_circle,
                          color: Colors.green,
                          size: 20,
                        ),
                        const SizedBox(width: 12),
                        Expanded(child: Text(feature)),
                      ],
                    ),
                  );
                }).toList(),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubscribeButton(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: SizedBox(
        width: double.infinity,
        child: ElevatedButton(
          onPressed: _isLoading ? null : _subscribe,
          style: ElevatedButton.styleFrom(
            backgroundColor: theme.colorScheme.primary,
            padding: const EdgeInsets.symmetric(vertical: 16),
          ),
          child: _isLoading
              ? const SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: Colors.white,
                  ),
                )
              : Text(
                  'Subscribe to ${_plans[_selectedPlanIndex].name} Plan',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
        ),
      ),
    );
  }

  Widget _buildFooter(ThemeData theme) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        children: [
          Text(
            'Cancel anytime. No hidden fees.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: theme.colorScheme.onSurface.withOpacity(0.7),
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 8),
          Text(
            'By subscribing, you agree to our Terms of Service and Privacy Policy.',
            style: theme.textTheme.bodySmall?.copyWith(
              color: theme.colorScheme.onSurface.withOpacity(0.5),
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildUsageItem(ThemeData theme, String title, String usage, String period) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(title),
          Text(
            '$usage/$period',
            style: theme.textTheme.bodyMedium?.copyWith(
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBenefitItem(ThemeData theme, String benefit) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        children: [
          Icon(
            Icons.check_circle,
            color: Colors.green,
            size: 20,
          ),
          const SizedBox(width: 12),
          Expanded(child: Text(benefit)),
        ],
      ),
    );
  }

  void _subscribe() async {
    setState(() => _isLoading = true);

    try {
      // Simulate payment process
      await Future.delayed(const Duration(seconds: 2));
      
      if (mounted) {
        // Show success dialog
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => AlertDialog(
            icon: Icon(
              Icons.check_circle,
              color: Colors.green,
              size: 48,
            ),
            title: const Text('Subscription Successful!'),
            content: Text(
              'Welcome to Premium! You now have access to all premium features.',
            ),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context); // Close dialog
                  context.pop(); // Go back to profile
                },
                child: const Text('Continue'),
              ),
            ],
          ),
        );
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Subscription failed: ${e.toString()}'),
            backgroundColor: Theme.of(context).colorScheme.error,
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isLoading = false);
      }
    }
  }
}

class SubscriptionPlan {
  final String name;
  final int price;
  final String duration;
  final int savings;
  final List<String> features;

  SubscriptionPlan({
    required this.name,
    required this.price,
    required this.duration,
    required this.savings,
    required this.features,
  });
}