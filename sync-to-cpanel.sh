#!/bin/bash

# Sync Master Branch Changes to cPanel Branch
# This script helps sync code changes from master (Docker) to cpanel branch

set -e  # Exit on any error

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}🔄 Syncing master changes to cpanel branch...${NC}"

# Check if we're in a git repository
if ! git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${RED}❌ Error: Not in a git repository${NC}"
    exit 1
fi

# Save current branch
CURRENT_BRANCH=$(git branch --show-current)
echo -e "📍 Current branch: ${YELLOW}$CURRENT_BRANCH${NC}"

# Check if we have uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo -e "${YELLOW}⚠️  You have uncommitted changes in $CURRENT_BRANCH${NC}"
    echo -e "Please commit or stash your changes first."
    read -p "Continue anyway? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        exit 1
    fi
fi

# Ensure we have both master and cpanel branches
if ! git show-ref --verify --quiet refs/heads/master; then
    echo -e "${RED}❌ Error: master branch not found${NC}"
    exit 1
fi

if ! git show-ref --verify --quiet refs/heads/cpanel; then
    echo -e "${RED}❌ Error: cpanel branch not found${NC}"
    exit 1
fi

# Switch to cpanel branch
echo -e "${BLUE}🔀 Switching to cpanel branch...${NC}"
git checkout cpanel

# Get the latest changes from master
echo -e "${BLUE}📥 Fetching latest changes...${NC}"
git fetch origin master 2>/dev/null || echo "Note: No origin remote configured"

# Show what will be merged
echo -e "${BLUE}📊 Changes that will be synced from master:${NC}"
git log --oneline --no-merges cpanel..master | head -10

read -p "Continue with merge? (Y/n): " -n 1 -r
echo
if [[ $REPLY =~ ^[Nn]$ ]]; then
    git checkout $CURRENT_BRANCH
    echo -e "${YELLOW}🚫 Sync cancelled${NC}"
    exit 0
fi

# Attempt to merge from master
echo -e "${BLUE}🔀 Merging changes from master...${NC}"
if git merge master --no-edit; then
    echo -e "${GREEN}✅ Merge completed successfully!${NC}"
else
    # Handle merge conflicts
    echo -e "${YELLOW}⚠️  Merge conflicts detected${NC}"
    echo -e "${BLUE}Conflicted files:${NC}"
    git status --porcelain | grep "^UU" | cut -c4-
    
    echo ""
    echo -e "${YELLOW}📝 Key files to keep cPanel-specific versions:${NC}"
    echo "  - backend/.env.example (cPanel database settings)"
    echo "  - backend/deploy.sh (deployment script)"
    echo "  - CPANEL_DEPLOYMENT.md"
    echo "  - server-hooks/* (git hooks)"
    echo "  - server-setup.sh"
    echo ""
    echo -e "${BLUE}Please resolve conflicts manually, then run:${NC}"
    echo "  git add <resolved-files>"
    echo "  git commit"
    echo "  git push server cpanel"
    echo ""
    echo -e "${YELLOW}Staying in cpanel branch for conflict resolution...${NC}"
    exit 1
fi

# Show summary of changes
echo -e "${BLUE}📋 Sync Summary:${NC}"
echo -e "  • Merged latest changes from master"
echo -e "  • Branch: ${GREEN}cpanel${NC}"
echo -e "  • Ready for deployment"

# Ask if user wants to deploy immediately
echo ""
read -p "Deploy to cPanel server now? (y/N): " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    # Check if server remote exists
    if git remote get-url server > /dev/null 2>&1; then
        echo -e "${BLUE}🚀 Deploying to cPanel server...${NC}"
        git push server cpanel
        echo -e "${GREEN}✅ Deployment initiated!${NC}"
        echo -e "${BLUE}🌐 Check: https://status.dreamcoderz.com/api/v1/health${NC}"
    else
        echo -e "${YELLOW}⚠️  Server remote not configured${NC}"
        echo "To deploy, run: git push server cpanel"
        echo "To configure remote, run: git remote add server user@status.dreamcoderz.com:/home/statusdreamcoder/repo.git"
    fi
fi

# Return to original branch if different
if [ "$CURRENT_BRANCH" != "cpanel" ]; then
    echo -e "${BLUE}🔙 Returning to $CURRENT_BRANCH branch...${NC}"
    git checkout $CURRENT_BRANCH
fi

echo -e "${GREEN}🎉 Sync process completed!${NC}"