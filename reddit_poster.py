import praw
import sys

r = praw.Reddit(username=sys.argv[5], password=sys.argv[6], user_agent="user", client_id=sys.argv[3], client_secret=sys.argv[4])

# Create a submission to /r/test
try:
    r.subreddit(sys.argv[7]).submit(sys.argv[2], url=sys.argv[1])
except praw.exceptions.APIException as error:
    print error

print "TRUE"

# Comment on a known submission
#submission = reddit.submission(url='https://www.reddit.com/comments/5e1az9')
#submission.reply('Super rad!')

# Reply to the first comment of a weekly top thread of a moderated community
#submission = next(reddit.subreddit('mod').top('week'))
#submission.comments[0].reply('An automated reply')

# Output score for the first 256 items on the frontpage
#for submission in reddit.front.hot(limit=256):
#    print(submission.score)

# Obtain the moderator listing for redditdev
#for moderator in reddit.subreddit('redditdev').moderator:
#    print(moderator)