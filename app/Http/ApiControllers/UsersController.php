<?php

namespace App\Http\ApiControllers;

use Auth;
use Dingo\Api\Exception\UpdateResourceFailedException;
use Gate;
use Illuminate\Http\Request;
use App\Transformers\UserTransformer;
use App\Models\User;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UsersController extends Controller
{
    public function me()
    {
        return $this->show(Auth::id());
    }

    public function show($id)
    {
        $user = $this->users
            ->autoWithRootColumns(User::$includable)
            ->find($id);

        // 在 Transformer 中返回 links
        $user->links = true;

        return $this->response()->item($user, new UserTransformer());
    }

    public function update(Request $request, $id)
    {
        $user = $this->users->find($id);

        if (Gate::denies('update', $user)) {
            throw new AccessDeniedHttpException();
        }

        try {
            $user = $this->users->update($request->all(), $id);

            return $this->response()->item($user, new UserTransformer());
        } catch (ValidatorException $e) {
            throw new UpdateResourceFailedException('Could not update user.', $e->getMessageBag()->all());
        }
    }
}
