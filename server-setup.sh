#!/bin/bash

# Server Setup Script for cPanel Deployment
# Run this script on your cPanel server to set up the deployment environment

SERVER_BASE="/home/statusdreamcoder"
REPO_DIR="$SERVER_BASE/repo.git"
LIVE_DIR="$SERVER_BASE/live"
BACKUP_DIR="$SERVER_BASE/backups"

echo "🚀 Setting up cPanel server for Tamil Status Creator deployment..."

# Create directory structure
echo "📁 Creating directory structure..."
mkdir -p $REPO_DIR
mkdir -p $LIVE_DIR
mkdir -p $BACKUP_DIR

# Initialize bare git repository
echo "🔧 Initializing bare git repository..."
cd $REPO_DIR
git init --bare

# Set up post-receive hook
echo "🪝 Setting up git post-receive hook..."
cp /path/to/post-receive $REPO_DIR/hooks/post-receive
chmod +x $REPO_DIR/hooks/post-receive

# Set proper permissions
echo "🔒 Setting proper permissions..."
chmod -R 755 $SERVER_BASE
chown -R $(whoami):$(whoami) $SERVER_BASE

echo "✅ Server setup completed!"
echo ""
echo "Next steps:"
echo "1. Add remote on local machine: git remote add server $(whoami)@status.dreamcoderz.com:$REPO_DIR"
echo "2. Deploy: git push server cpanel"
echo "3. Configure database credentials in $LIVE_DIR/backend/.env"
echo ""
echo "Directories created:"
echo "  📦 Repository: $REPO_DIR"
echo "  🌐 Live site:  $LIVE_DIR"
echo "  💾 Backups:    $BACKUP_DIR"